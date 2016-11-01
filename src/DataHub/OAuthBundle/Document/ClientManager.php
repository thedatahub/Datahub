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
        return $this->findClientBy(array(
            'randomId' => $publicId,
        ));
    }
}
