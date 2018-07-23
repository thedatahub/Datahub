<?php

namespace DataHub\OAuthBundle\DataFixtures;

use DataHub\SharedBundle\DataFixtures\EnvironmentSpecificDataFixture as AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use DataHub\OAuthBundle\Document\Client;

class ClientFixtures extends AbstractFixture implements FixtureInterface
{
    const DEFAULT_CLIENT_PUBLIC_ID = 'slightlylesssecretpublicid';
    const DEFAULT_CLIENT_SECRET_ID = 'supersecretsecretphrase';

    /**
     * {@inheritDoc}
     */
    protected function doLoad(ObjectManager $manager)
    {
        $entity = new Client();
        $entity->setLabel('TestClient');
        $entity->setAllowedGrantTypes(['client_credentials', 'refresh_token', 'token', 'password']);
        $entity->setRedirectUris([]);
        $entity->setRandomId(static::DEFAULT_CLIENT_PUBLIC_ID);
        $entity->setSecret(static::DEFAULT_CLIENT_SECRET_ID);

        $manager->persist($entity);
        $manager->flush();
    }
}
