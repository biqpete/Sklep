<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 06/08/2018
 * Time: 22:36
 */

namespace App\Subscribers;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class UserLocaleSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if(!is_null($user->getLocale())) {
            $this->session->set('_locale', $user->getLocale());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
          SecurityEvents::INTERACTIVE_LOGIN => [
              ['onLogin', 15]
          ]
        ];
    }
}