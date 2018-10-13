<?php

namespace DataHub\UserBundle;

final class DataHubUserEvents
{
    /**
     * The REGISTRATION_SUCCESS event occurs when the registration form is submitted successfully.
     *
     * @Event("DataHub\UserBundle\Event\FormEvent")
     */
    const REGISTRATION_SUCCESS = 'datahub_user.registration.success';
    
    /**
     * The REGISTRATION_CONFIRMED event occurs when the registration was confirmed succesfully.
     * 
     * @Event("Datahub\UserBundle\Event\FilterUserResponseEvent")
     */
    const REGISTRATION_CONFIRMED = 'datahub_user.registration.confirmed';
}
