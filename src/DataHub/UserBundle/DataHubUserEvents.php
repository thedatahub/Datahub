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

    /**
     * The RESET_SUCCESS event occurs when the reset form was submitted succesfully.
     * 
     * @Event("Datahub\UserBundle\Event\FilterUserEvent")
     */
    const RESET_SUCCESS = 'datahub_user.reset.success';

    /**
     * The RESET_CONFIRMED event occurs when the reset was confirmed succesfully.
     * 
     * @Event("Datahub\UserBundle\Event\FilterUserResponseEvent")
     */
    const RESET_CONFIRMED = 'datahub_user.reset.confirmed';
}
