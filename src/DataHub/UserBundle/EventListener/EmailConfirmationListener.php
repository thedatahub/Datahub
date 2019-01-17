<?php

namespace DataHub\UserBundle\EventListener;

use DataHub\UserBundle\DataHubUserEvents;
use DataHub\UserBundle\Event\FormEvent;
use DataHub\UserBundle\Mailer\MailerInterface;
use DataHub\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EmailConfirmationListener implements EventSubscriberInterface
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
            DataHubUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
            DataHubUserEvents::INSTALLATION_SUCCESS => 'onInstallationSuccess',
        );
    }

    /**
     * Callback
     * 
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();

        $user->setEnabled(false);
        if (null == $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->mailer->sendConfirmationEmailMessage($user);
    }

    /**
     * Callback
     * 
     * @param FormEvent $event
     */
    public function onInstallationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();

        $user->setEnabled(false);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPlainPassword($this->tokenGenerator->generateToken());

        if (null == $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->mailer->sendConfirmationEmailMessage($user);
    }
}