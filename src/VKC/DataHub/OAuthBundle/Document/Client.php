<?php

namespace VKC\DataHub\OAuthBundle\Document;

use FOS\OAuthServerBundle\Document\Client as BaseClient;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

use VKC\DataHub\SharedBundle\Document\Traits as Traits;

/**
 * @ODM\Document
 * @MongoDBUnique({"label"})
 */
class Client extends BaseClient
{
    use Traits\LabelableTrait;

    /**
     * @ODM\Id(strategy="auto")
     */
    protected $id;

    /**
     * @ODM\ReferenceMany(targetDocument="AuthCode", mappedBy="client", orphanRemoval=true)
     */
    protected $authcodes;

    /**
     * @ODM\ReferenceMany(targetDocument="AccessToken", mappedBy="client", orphanRemoval=true)
     */
    protected $accessTokens;

    /**
     * @ODM\ReferenceMany(targetDocument="RefreshToken", mappedBy="client", orphanRemoval=true)
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
     * {@inheritdoc}
     */
    public function getPublicId()
    {
        return $this->getRandomId();
    }

    /**
     * To string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }

    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get guessed user.
     *
     * @return UserInterface|null
     */
    public function getGuessedUser()
    {
        if ($this->accessTokens->count()) {
            return $this->accessTokens->first()->getUser();
        }

        if ($this->refreshTokens->count()) {
            return $this->refreshTokens->first()->getUser();
        }

        if ($this->authCodes->count()) {
            return $this->authCodes->first()->getUser();
        }

        return null;
    }
}
