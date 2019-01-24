<?php

namespace DataHub\OAuthBundle\DTO;

use DataHub\OAuthBundle\Document\Client;
use DataHub\OAuthBundle\DTO\ClientEditData;
use Doctrine\Common\Persistence\ManagerRegistry;

class ClientEditAssembler
{   
    private $managerRegistry;
    
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function createDTO(Client $client)
    {
        $repository = $this->managerRegistry->getRepository('DataHubOAuthBundle:Client');
        $clientEditData = new ClientEditData($repository);
        
        $clientEditData->setApplicationName($client->getApplicationName());
        $redirectUris = $client->getRedirectUris();
        $redirectUris = array_shift($redirectUris);
        $clientEditData->setRedirectUris($redirectUris);
        $clientEditData->setAllowedGrantTypes($client->getAllowedGrantTypes());

        $clientEditData->setUser($client->getUser());

        return $clientEditData;
    }

    public function updateProfile(Client $client, ClientEditData $clientEditData)
    {
        $client->setApplicationName($clientEditData->getApplicationName());
        // pass redirectUris as an array because that's what the OAuth model expects.
        // We only ask for one redirectUri though.
        $client->setRedirectUris(array($clientEditData->getRedirectUris()));
        $client->setAllowedGrantTypes($clientEditData->getAllowedGrantTypes());
        $client->setUser($clientEditData->getUser());

        return $client;
    }
}
