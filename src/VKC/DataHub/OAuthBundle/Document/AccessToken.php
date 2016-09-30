<?php

namespace VKC\DataHub\OAuthBundle\Document;

use FOS\OAuthServerBundle\Document\AccessToken as BaseAccessToken;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

use VKC\DataHub\SharedBundle\Document\Traits as Traits;

/**
 * @ODM\Document
 */
class AccessToken extends BaseAccessToken
{
    /**
     * @ODM\Id(strategy="auto")
     */
    protected $id;

    /**
     * @var ClientInterface
     *
     * @ODM\ReferenceOne(targetDocument="Client")
     */
    protected $client;

    /**
     * @var VKC\DataHub\UserBundle\Document\User
     *
     * @ODM\ReferenceOne(targetDocument="VKC\DataHub\UserBundle\Document\User")
     */
    protected $user;

    /**
     * Get client.
     *
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set client.
     *
     * @param ClientInterface $client
     * @return AccessToken
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get user.
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param UserInterface $user
     * @return AccessToken
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }
}
