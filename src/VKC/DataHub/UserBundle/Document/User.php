<?php

namespace VKC\DataHub\UserBundle\Document;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

use VKC\DataHub\SharedBundle\Document\Traits;

/**
 * @ODM\Document
 *
 * @MongoDBUnique({"username"})
 * @MongoDBUnique({"email"})
 *
 * @Serializer\ExclusionPolicy("all")
 */
class User extends BaseUser
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
    protected $id;

    /**
     * @var string $username
     *
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    protected $username;

    /**
     * @var string $email
     *
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     *
     * @Assert\NotBlank
     * @Assert\Email
     */
    protected $email;

    /**
     * @var boolean $enabled
     *
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     */
    protected $enabled;

    /**
     * @var array $roles
     *
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     */
    protected $roles;

    /**
     * @ODM\ReferenceMany(targetDocument="VKC\DataHub\OAuthBundle\Document\AuthCode", mappedBy="user", orphanRemoval=true)
     */
    protected $authcodes;

    /**
     * @ODM\ReferenceMany(targetDocument="VKC\DataHub\OAuthBundle\Document\AccessToken", mappedBy="user", orphanRemoval=true)
     */
    protected $accessTokens;

    /**
     * @ODM\ReferenceMany(targetDocument="VKC\DataHub\OAuthBundle\Document\RefreshToken", mappedBy="user", orphanRemoval=true)
     */
    protected $refreshTokens;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }
}
