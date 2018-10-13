<?php

namespace DataHub\UserBundle\EventListener;

use DataHub\UserBundle\DataHubUserEvents;
use DataHub\UserBundle\Event\FilterUserResponseEvent;
use DataHub\UserBundle\Security\LoginManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AuthenticationListener implements EventSubscriberInterface
{
    /**
     * @var LoginManagerInterface
     */
    private $loginManager;

    /**
     * @var String
     */
    private $firewallName;

    /**
     * AuthenticationListener constructor
     * 
     * @param LoginManagerInterface $loginManager An instance of LoginManagerInterface
     * @param String $firewallName The name of the firewall
     */
    public function __construct(LoginManagerInterface $loginManager, $firewallName)
    {
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            DataHubUserEvents::REGISTRATION_CONFIRMED => 'onAuthenticate',
        ];
    }

    /**
     * 
     */
    public function onAuthenticate(FilterUserResponseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        try {
            $this->loginManager->logInUser($this->firewallName, $event->getUser(), $event->getResponse());
        } catch (AccountStatusException $ex) {
            // Don't authenticate users that didn't pass the check.
            // (not enabled, expired, etc.)
        }
    }
}