<?php

namespace DataHub\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Validator\Constraints as Assert;

use DataHub\SharedBundle\Document\Traits;

/** 
 * @ODM\Document(
 *   collection="Users", 
 *   repositoryClass="DataHub\UserBundle\Repository\UserRepository"
 * ) 
 * @Serializer\ExclusionPolicy("all")
 */
class User implements UserInterface
{
    use Traits\TimestampableTrait;

    /**
     * @var string $id
     *
     * @ODM\Id(strategy="auto")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     */
    private $id;

    /**
     * @var string $username
     *
     * @ODM\Field(type="string")
     * 
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $username;

    /**
     * @var string $firstName
     *
     * @ODM\Field(type="string")
     * 
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $firstName;

    /**
     * @var string $lastName
     *
     * @ODM\Field(type="string")
     * 
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $lastName;

    /**
     * @var string $password
     * 
     * @ODM\Field(type="string")
     * 
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
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
    // private $plainPassword;

    /**
     * @var string $email
     * 
     * @ODM\Field(type="string")
     * 
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     *
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @var boolean $enabled
     *
     * @ODM\Field(type="boolean")
     * 
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     */
    private $enabled;

    /**
     * @var string $confirmationToken
     * 
     * @ODM\Field(type="string")
     * 
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     * 
     * @Assert\Type("String")
     */
    private $confirmationToken;

    /**
     * @var array $roles
     * 
     * @ODM\Field(type="collection")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     */
    private $roles;

    /**
     * @ODM\ReferenceMany(targetDocument="DataHub\OAuthBundle\Document\AuthCode", mappedBy="user", orphanRemoval=true)
     */
    private $authcodes;

    /**
     * @ODM\ReferenceMany(targetDocument="DataHub\OAuthBundle\Document\AccessToken", mappedBy="user", orphanRemoval=true)
     */
    private $accessTokens;

    /**
     * @ODM\ReferenceMany(targetDocument="DataHub\OAuthBundle\Document\RefreshToken", mappedBy="user", orphanRemoval=true)
     */
    private $refreshTokens;

    public function getId()
    {
        return $this->id;
    }

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

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
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

        // give everyone ROLE_CONSUMER!
        if (!in_array('ROLE_CONSUMER', $roles)) {
            $roles[] = 'ROLE_CONSUMER';
        }

        return $roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }
}
