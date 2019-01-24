<?php

namespace DataHub\OAuthBundle\DTO;

use DataHub\OAuthBundle\Repository\ClientRepository;
use DataHub\UserBundle\Document\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** 
 * DTO for Client
 */
class ClientCreateData
{
    private $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var string $applicationName
     *
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="alnum",
     *     message="The value {{ value }} should only consist of alphanumeric characters."
     * )
     */
    private $applicationName;

    /**
     * @var string $redirectUris
     *
     * @Assert\Type("string")
     */
    private $redirectUris;

    /**
     * @var string $allowedGrantTypes
     *
     * @Assert\NotBlank
     * @Assert\Type("array")
     */
    private $allowedGrantTypes;

    /**
     * @var User $user
     */
    private $user;
    
    public function setApplicationName($applicationName)
    {
        $this->applicationName = $applicationName;
    }

    public function getApplicationName()
    {
        return $this->applicationName;
    }

    public function setRedirectUris($redirectUris)
    {
        $this->redirectUris = $redirectUris;
    }

    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    public function setAllowedGrantTypes($allowedGrantTypes)
    {
        $this->allowedGrantTypes = $allowedGrantTypes;
    }

    public function getAllowedGrantTypes()
    {
        return $this->allowedGrantTypes;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * Custom form constraints.
     * 
     * @param ExecutionContextInterface $context Context
     * 
     * @return void
     * 
     * @todo Workaround. Deprecate this validation by setting a Unique on the
     *   Mongodb document. Tried setting a unique index on the with the MongoDB 
     *   Unique constraint, but it doesn't work. Check again when upgrading
     *   Symfony.
     * 
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // $email = $this->getEmail();

        // if ($this->repository->findOneBy(array('email' => $email))) {
        //     $context->buildViolation('A user with this email address already exists.')
        //         ->atPath('email')
        //         ->addViolation();
        // }

        // $username = $this->getUsername();

        // if ($this->repository->findOneBy(array('username' => $username))) {
        //     $context->buildViolation('A user with this username already exists.')
        //         ->atPath('username')
        //         ->addViolation();
        // }
    }
}
