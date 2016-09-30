<?php

namespace VKC\DataHub\OAuthBundle\DataFixtures\MongoDB;

use VKC\DataHub\SharedBundle\DataFixtures\EnvironmentSpecificDataFixture as AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use VKC\DataHub\OAuthBundle\Document\Client;

class LoadClientData extends AbstractFixture implements FixtureInterface
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
        $entity->setAllowedGrantTypes(['authorization_code', 'refresh_token', 'token', 'password']);
        $entity->setRedirectUris(['http://localhost', 'https://vkcdatahub.loc']);
        $entity->setRandomId(static::DEFAULT_CLIENT_PUBLIC_ID);
        $entity->setSecret(static::DEFAULT_CLIENT_SECRET_ID);

        $manager->persist($entity);
        $manager->flush();
    }
}
