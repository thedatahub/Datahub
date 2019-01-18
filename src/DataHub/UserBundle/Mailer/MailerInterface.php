<?php

namespace DataHub\UserBundle\Mailer;

interface MailerInterface
{
    /**
     * Send a confirmation message to confirm the account.
     * 
     * @param $user
     */
    public function sendConfirmationEmailMessage($user);
}
