<?php

namespace DataHub\UserBundle\DTO;

use DataHub\UserBundle\Document\User;
use DataHub\UserBundle\DTO\ProfileEditData;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class ProfileEditAssembler
{
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoder $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function createDTO(User $user)
    {
        $profileEditData = new ProfileEditData();

        $profileEditData->setUsername($user->getUsername());
        $profileEditData->setEmail($user->getEmail());
        $profileEditData->setFirstName($user->getFirstName());
        $profileEditData->setLastName($user->getLastName());
        $profileEditData->setEnabled($user->getEnabled());
        $profileEditData->setRoles($user->getRoles());

        return $profileEditData;
    }

    public function updateProfile(User $user, ProfileEditData $profileEditData)
    {
        $user->setUsername($profileEditData->getUsername());
        $user->setEmail($profileEditData->getEmail());
        $user->setFirstName($profileEditData->getFirstName());
        $user->setLastName($profileEditData->getLastName());
        $user->setEnabled($profileEditData->getEnabled());
        $user->setRoles($profileEditData->getRoles());

        if ($profileEditData->getPlainPassword()) {
            $encoded = $this->passwordEncoder->encodePassword(
                $user,
                $profileEditData->getPlainPassword()
            );

            $user->setPassword($encoded);
        }

        return $user;
    }
}
