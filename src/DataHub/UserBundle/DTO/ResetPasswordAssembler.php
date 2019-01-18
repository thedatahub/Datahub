<?php

namespace DataHub\UserBundle\DTO;

use Doctrine\Common\Persistence\ManagerRegistry;
use DataHub\UserBundle\DTO\ResetPasswordData;
use DataHub\UserBundle\Document\User;

class ResetPasswordAssembler
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    public function createDTO()
    {
        $repository = $this->managerRegistry->getRepository('DataHubUserBundle:User');
        $resetPasswordData = new ResetPasswordData($repository);

        return $resetPasswordData;
    }

    public function updateProfile(User $user, ResetPasswordData $resetPasswordData)
    {
        $user->setConfirmationToken($resetPasswordData->getConfirmationToken());

        return $user;
    }
}
