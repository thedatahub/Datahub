<?php

namespace DataHub\OAuthBundle\Document;

use FOS\OAuthServerBundle\Document\ClientManager as BaseClientManager;
use FOS\OAuthServerBundle\Model\ClientInterface;

/**
 * {@inheritDoc}
 */
class ClientManager extends BaseClientManager
{
    /**
     * {@inheritdoc}
     */
    public function findClientByPublicId($publicId)
    {
        // We override this method to perform a search based on
        // just randomId instead of a combination of randomId and 
        // the client Id which was issued by MongoDB. Exposing
        // the client Id to the public is a security concern.
        return $this->findClientBy(array(
            'randomId' => $publicId,
        ));
    }
}
