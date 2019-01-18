<?php

namespace DataHub\UserBundle\DTO;

use DataHub\UserBundle\Document\User;
use DataHub\UserBundle\DTO\ProfileCreateData;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class ProfileCreateAssembler
{
    private $passwordEncoder;
    
    private $managerRegistry;

    public function __construct(UserPasswordEncoder $passwordEncoder, ManagerRegistry $managerRegistry)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->managerRegistry = $managerRegistry;
    }

    public function createDTO(User $user)
    {
        $repository = $this->managerRegistry->getRepository('DataHubUserBundle:User');
        $profileCreateData = new ProfileCreateData($repository);

        $profileCreateData->setUsername($user->getUsername());
        $profileCreateData->setEmail($user->getEmail());
        $profileCreateData->setFirstName($user->getFirstName());
        $profileCreateData->setLastName($user->getLastName());
        $profileCreateData->setEnabled($user->getEnabled());
        $profileCreateData->setConfirmationToken($user->getConfirmationToken());
        $profileCreateData->setRoles($user->getRoles());

        return $profileCreateData;
    }

    public function updateProfile(User $user, ProfileCreateData $profileCreateData)
    {
        $user->setUsername($profileCreateData->getUsername());
        $user->setEmail($profileCreateData->getEmail());
        $user->setFirstName($profileCreateData->getFirstName());
        $user->setLastName($profileCreateData->getLastName());
        $user->setEnabled($profileCreateData->getEnabled());
        $user->setConfirmationToken($profileCreateData->getConfirmationToken());
        $user->setRoles($profileCreateData->getRoles());

        if ($profileCreateData->getPlainPassword()) {
            $encoded = $this->passwordEncoder->encodePassword(
                $user,
                $profileCreateData->getPlainPassword()
            );

            $user->setPassword($encoded);
        }

        return $user;
    }
}
