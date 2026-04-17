<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Security;

use Psr\Log\LoggerInterface;
use STG\DEIM\Security\Bundle\CasBundle\Exception\CasException;
use STG\DEIM\Security\Bundle\CasBundle\Lib\CAS;
use STG\DEIM\Security\Bundle\CasBundle\Lib\Client;
use STG\DEIM\Security\Bundle\CasBundle\Lib\Storage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class CasAuthenticator extends AbstractAuthenticator implements
    InteractiveAuthenticatorInterface,
    AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly CAS $cas,
        private readonly Client $client,
        private readonly Storage $storage,
        private readonly UserProviderInterface $userProvider,
        private readonly string $checkPath,
        private readonly string $failurePath,
        private readonly HttpUtils $httpUtils,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * Punto de entrada: redirige al login de CAS cuando el usuario no está autenticado.
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $serviceUrl = $this->httpUtils->generateUri($request, $this->checkPath);
        $this->logger?->info('CAS: redirigiendo a login, service=' . $serviceUrl);

        return new RedirectResponse($this->cas->getLoginUrl($serviceUrl));
    }

    /**
     * Activa el autenticador sólo en la ruta de check con ticket presente.
     */
    public function supports(Request $request): ?bool
    {
        return $this->httpUtils->checkRequestPath($request, $this->checkPath)
            && $request->query->has('ticket');
    }

    /**
     * Valida el ticket contra el servidor CAS y retorna el Passport.
     */
    public function authenticate(Request $request): Passport
    {
        $serviceUrl = $this->httpUtils->generateUri($request, $this->checkPath);
        $ticket = $request->query->getString('ticket');

        try {
            if ($this->cas->isProxy()) {
                $callbackUrl = $serviceUrl . '?callbackProxy=true';
                $result = $this->client->validateServiceTicket($serviceUrl, $ticket, $callbackUrl);
            } else {
                $result = $this->client->validateServiceTicket($serviceUrl, $ticket);
            }
        } catch (CasException $e) {
            $this->logger?->error('CAS: validación de ticket fallida — ' . $e->getMessage());
            throw new AuthenticationException($e->getMessage(), 0, $e);
        }

        $username = $result['user'];
        $pgtiou   = $result['pgtiou'] ?? null;

        $this->logger?->info(sprintf('CAS: ticket válido para "%s"', $username));

        return new SelfValidatingPassport(
            new UserBadge($username, function (string $identifier) use ($pgtiou): object {
                $user = $this->userProvider->loadUserByIdentifier($identifier);

                if ($this->cas->isProxy() && $pgtiou !== null) {
                    // El PGT queda almacenado en Storage; se puede recuperar
                    // mediante $storage->getPgt($pgtiou) cuando se necesite.
                    $this->logger?->info('CAS proxy: PGT recibido para ' . $identifier);
                }

                return $user;
            })
        );
    }

    /**
     * Tras autenticación exitosa, redirige a la URL original o a /home.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger?->info(sprintf('CAS: autenticación exitosa para "%s"', $token->getUserIdentifier()));

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            $this->removeTargetPath($request->getSession(), $firewallName);
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse('/home');
    }

    /**
     * Tras fallo de autenticación, redirige al logout de CAS apuntando a la ruta de error.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger?->warning('CAS: fallo de autenticación — ' . $exception->getMessage());

        $failureUrl = $this->httpUtils->generateUri($request, $this->failurePath);

        return new RedirectResponse($this->cas->getLogoutUrl($failureUrl));
    }

    public function isInteractive(): bool
    {
        return true;
    }
}
