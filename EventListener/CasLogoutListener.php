<?php

namespace STG\DEIM\Security\Bundle\CasBundle\EventListener;

use STG\DEIM\Security\Bundle\CasBundle\Lib\CAS;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Intercepta el evento de logout de Symfony y redirige al logout de CAS,
 * de forma que la sesión SSO también se cierre en el servidor CAS.
 */
class CasLogoutListener
{
    public function __construct(
        private readonly CAS $cas,
        private readonly HttpUtils $httpUtils,
    ) {}

    public function onLogout(LogoutEvent $event): void
    {
        $request    = $event->getRequest();
        $serviceUrl = $this->httpUtils->generateUri($request, '/');

        $event->setResponse(
            new RedirectResponse($this->cas->getLogoutUrl($serviceUrl))
        );
    }
}
