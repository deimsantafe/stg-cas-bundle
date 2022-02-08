<?php

namespace Stg\Bundle\CasBundle\Security\EventListener;

use Stg\Bundle\CasBundle\Service\CasService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class SessionLogoutListener implements EventSubscriberInterface
{
    private $cas;

    public function __construct(CasService $cas)
    {
        $this->cas = $cas;
    }

    public function onLogout(LogoutEvent $event): void
    {
        $this->cas->logout($event->getRequest());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
