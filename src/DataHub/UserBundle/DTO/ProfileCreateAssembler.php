<?php

namespace DataHub\UserBundle\DTO;

use DataHub\UserBundle\Document\User;
use DataHub\UserBundle\DTO\ProfileCreateData;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class ProfileCreateAssembler
{
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoder $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function createDTO(User $user)
    {
        $profileCreateData = new ProfileCreateData();

        $profileCreateData->setUsername($user->getUsername());
        $profileCreateData->setEmail($user->getEmail());
        $profileCreateData->setFirstName($user->getFirstName());
        $profileCreateData->setLastName($user->getLastName());
        $profileCreateData->setEnabled($user->getEnabled());
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
