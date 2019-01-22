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
 */
class Client extends BaseClient
{
    /**
     * @ODM\Id(strategy="auto")
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     */
    protected $randomId;

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
     * We use RandomID as a OAuth Client Identifier as an added layer
     * against phising attacks.
     * 
     * Per RFC 6749 Section 2.2:
     * 
     *  The authorization server issues the registered client a client
     *  identifier -- a unique string representing the registration
     *  information provided by the client.
     * 
     * We use the $randomId property of the BaseClient which is set
     * at object instantiation. We do not perform a validation against
     * uniqueness. This value is based on a string generated through
     * random_bytes(32). This leaves us with a 2^256 possibilities
     * of the same string being generated twice.
     */
    public function getPublicId()
    {
        return $this->getRandomId();
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
