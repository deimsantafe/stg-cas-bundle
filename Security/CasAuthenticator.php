<?php

namespace Stg\Bundle\CasGuardBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Stg\Bundle\CasGuardBundle\Service\CasService;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CasAuthenticator extends AbstractGuardAuthenticator implements LogoutSuccessHandlerInterface
{
    private $cas;
    private $security;

    public function __construct(CasService $cas, Security $security)
    {
        $this->cas = $cas;
        $this->security = $security;
    }

    public function supports(Request $request)
    {
        if ($this->security->getUser()) {
            return false;
        }
                
        return true;
    }

    public function getCredentials(Request $request)
    {
        return $this->cas->Authenticate();
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $token->setAttributes($this->cas->getAttributes());
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->cas->loginFailure($request, $exception);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        //The URL have to be completed by the current request uri,
        // because Cas Server need to know where redirect user after authentication.
        return new RedirectResponse($this->cas->getUri() . $request->getUri());
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function onLogoutSuccess(Request $request)
    {
        $this->cas->logout($request);
    }

}
