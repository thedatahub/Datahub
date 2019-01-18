<?php

namespace DataHub\UserBundle\EventListener;

use DataHub\UserBundle\DataHubUserEvents;
use DataHub\UserBundle\Event\FilterUserEvent;
use DataHub\UserBundle\Mailer\MailerInterface;
use DataHub\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EmailResetConfirmationListener implements EventSubscriberInterface
{
    private $router;

    private $session;

    private $tokenGenerator;

    private $mailer;

    public function __construct(UrlGeneratorInterface $router, SessionInterface $session, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer)
    {
        $this->router = $router;
        $this->session = $session;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            DataHubUserEvents::RESET_SUCCESS => 'onResetSuccess',
        );
    }

    /**
     * Callback
     * 
     * @param FilterUserEvent $event
     */
    public function onResetSuccess(FilterUserEvent $event)
    {
        $user = $event->getUser();

        $user->setEnabled(false);
        if (null == $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->mailer->sendResetConfirmationEmailMessage($user);
    }
}