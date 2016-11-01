<?php

namespace DataHub\OAuthBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Doctrine\ODM\MongoDB\DocumentManager;

use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\AccessTokenInterface;

/**
 * The OAuthService contains some helpers for grabbing
 * the Client, various Tokens and User that are currently
 * in use.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class OAuthService
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * Set tokenStorage.
     *
     * @param TokenStorageInterface $tokenStorage
     * @return OAuthService
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * Set documentManager.
     *
     * @param DocumentManager $documentManager
     * @return OAuthService
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;

        return $this;
    }

    /**
     * Get the Client that's currently in use.
     *
     * @return ClientInterface|null
     */
    public function getClient()
    {
        $accessToken = $this->getAccessToken();

        if ($accessToken) {
            $client = $accessToken->getClient();

            return $client;
        }

        return null;
    }

    /**
     * Get the AccessToken that is currently in use.
     *
     * @return AccessTokenInterface|null
     */
    public function getAccessToken()
    {
        $accessTokenString = $this->getAccessTokenString();

        if ($accessTokenString) {
            $accessToken = $this->documentManager
                ->createQueryBuilder('DataHubOAuthBundle:AccessToken')
                ->field('token')->equals($accessTokenString)
                ->getQuery()
                ->getSingleResult();

            return $accessToken;
        }

        return null;
    }

    /**
     * Get the access token currently in use as a string.
     *
     * @return string|null
     */
    public function getAccessTokenString()
    {
        $securityToken = $this->getSecurityToken();

        if (method_exists($securityToken, 'getToken')) {
            $accessTokenString = $securityToken->getToken();

            if (is_string($accessTokenString)) {
                return $accessTokenString;
            }
        }

        return null;
    }

    /**
     * Get the security token.
     *
     * @return TokenInterface
     */
    protected function getSecurityToken()
    {
        return $this->tokenStorage->getToken();
    }
}
