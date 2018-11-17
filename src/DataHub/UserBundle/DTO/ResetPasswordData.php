<?php

namespace DataHub\UserBundle\DTO;

use DataHub\UserBundle\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** 
 * DTO for Reset Password form
 */
class ResetPasswordData
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var string $email
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $email;

    /**
     * @var string $confirmationToken
     *
     * @Assert\Type("String")
     */
    private $confirmationToken;

    /**
     * @var boolean $enabled
     */
    private $enabled;

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        $email = $this->getEmail();

        if (!$this->repository->findOneBy(array('email' => $email))) {
            $context->buildViolation('No user with this email-address exists.')
                ->atPath('email')
                ->addViolation();
        }
    }
}