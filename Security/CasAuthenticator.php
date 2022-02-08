<?php

namespace Stg\Bundle\CasBundle\Security;

use Stg\Bundle\CasBundle\Service\CasService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class CasAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private $cas;
    private $security;

    public function __construct(CasService $cas, Security $security)
    {
        $this->cas = $cas;
        $this->security = $security;
    }

    public function supports(Request $request): ?bool
    {
        if ($this->security->getUser()) {
            return false;
        }

        if($request->isXmlHttpRequest()) {
            throw new AuthenticationException('Unauthorized', 401);  
        } 
                  
        return true;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        // Si el llamado es ajax devuelve un 401
        if($request->isXmlHttpRequest()) {
            return new JsonResponse($authException->getMessage(), $authException->getCode());
        }

        //The URL have to be completed by the current request uri,
        // because Cas Server need to know where redirect user after authentication.
        return new RedirectResponse($this->cas->getUri() . $request->getUri());
    }

    public function authenticate(Request $request): Passport
    {
        $user = $this->cas->Authenticate();
        return new SelfValidatingPassport(new UserBadge($user));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $token->setAttributes($this->cas->getAttributes());
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if($request->isXmlHttpRequest()) {
            $data = array(
                'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            );   
            return new JsonResponse($data, $exception->getCode());
        }
        
        return $this->cas->loginFailure($request, $exception);
    }
}
