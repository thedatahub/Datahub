<?php

namespace DataHub\OAuthBundle\DTO;

use DataHub\OAuthBundle\Document\Client;
use DataHub\OAuthBundle\DTO\ClientCreateData;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClientCreateAssembler
{   
    private $managerRegistry;
    
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        $this->tokenStorage = $tokenStorage;
    }

    public function createDTO(Client $client)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $repository = $this->managerRegistry->getRepository('DataHubOAuthBundle:Client');
        $clientCreateData = new ClientCreateData($repository);
        
        $clientCreateData->setApplicationName($client->getApplicationName());
        $clientCreateData->setRedirectUris($client->getRedirectUris());
        $clientCreateData->setAllowedGrantTypes($client->getAllowedGrantTypes());

        $clientCreateData->setUser($currentUser);

        return $clientCreateData;
    }

    public function updateProfile(Client $client, ClientCreateData $clientCreateData)
    {
        $client->setApplicationName($clientCreateData->getApplicationName());
        // pass redirectUris as an array because that's what the OAuth model expects.
        // We only ask for one redirectUri though.
        $client->setRedirectUris(array($clientCreateData->getRedirectUris()));
        $client->setAllowedGrantTypes($clientCreateData->getAllowedGrantTypes());
        $client->setUser($clientCreateData->getUser());

        return $client;
    }
}
