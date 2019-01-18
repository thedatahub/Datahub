<?php

namespace DataHub\OAuthBundle\Document;

use FOS\OAuthServerBundle\Document\Client as BaseClient;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Validator\Constraints as Assert;


use DataHub\SharedBundle\Document\Traits as Traits;

/**
 * @ODM\Document
 * @MongoDBUnique({"label"})
 * @MongoDBUnique({"clientCode"})
 */
class Client extends BaseClient
{
    use Traits\LabelableTrait;

    /**
     * @ODM\Id(strategy="auto")
     */
    protected $id;

    /**
     * @ODM\ReferenceOne(targetDocument="DataHub\UserBundle\Document\User")
     */
    protected $user;

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
     * @ODM\Field(type="string")
     */
    protected $clientCode;

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
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
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

    /**
     * Set clientCode
     *
     * @param string $clientCode
     * @return self
     */
    public function setClientCode($clientCode)
    {
        /*
         * If the ID is already set, it means that this record is already in DB
         *  and therefore clientCode can't be changed.
         */
        if (!$this->getId() || empty($this->getClientCode())) {
            $this->clientCode = $clientCode;
        }
        return $this;
    }

    /**
     * Get clientCode
     *
     * @return string $clientCode
     */
    public function getClientCode()
    {
        return $this->clientCode;
    }
}
