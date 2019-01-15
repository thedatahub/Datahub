<?php

namespace DataHub\UserBundle\DTO;

use DataHub\UserBundle\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** 
 * DTO for Profile
 */
class ProfileCreateData
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var string $username
     *
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="alnum",
     *     message="The value {{ value }} should only consist of alphanumeric characters."
     * )
     */
    private $username;

    /**
     * @var string $firstName
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $firstName;

    /**
     * @var string $lastName
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $lastName;

    /**
     * @var string $password
     * 
     * @Assert\Type("String")
     */
    private $password;

    /**
     * @var string $plainPassword
     * 
     * A non-persisted field used to create the encoded password.
     *
     * @Assert\NotBlank(message="Password cannot be empty", groups={"Create"})
     */
    private $plainPassword;

    /**
     * @var string $email
     *
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @var boolean $enabled
     */
    private $enabled;

    /**
     * @var string $confirmationToken
     *
     * @Assert\Type("String")
     */
    private $confirmationToken;

    /**
     * @var array $roles
     */
    private $roles;


    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }
    
    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = $this->roles;

        if (!is_array($roles)) {
            $roles = array();
        }

        return $roles;
    }

    public function setRoles(array $roles)
    {
        // give everyone ROLE_USER!
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }

        $this->roles = $roles;
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
        $email = $this->getEmail();

        if ($this->repository->findOneBy(array('email' => $email))) {
            $context->buildViolation('A user with this email address already exists.')
                ->atPath('email')
                ->addViolation();
        }

        $username = $this->getUsername();

        if ($this->repository->findOneBy(array('username' => $username))) {
            $context->buildViolation('A user with this username already exists.')
                ->atPath('username')
                ->addViolation();
        }
    }
}
