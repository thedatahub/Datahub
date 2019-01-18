<?php

namespace DataHub\UserBundle\DataFixtures;

use DataHub\SharedBundle\DataFixtures\EnvironmentSpecificDataFixture as AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use DataHub\UserBundle\Document\User;

class UserFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    const DEFAULT_ADMIN_USERNAME = 'admin';
    const DEFAULT_ADMIN_PASSWORD = 'datahub';

    /**
     * {@inheritDoc}
     */
    protected function doLoad(ObjectManager $manager)
    {
        $userAdmin = new User;
        $assembler = $this->container->get('datahub.security.user.dto.profile_create_assembler');
        
        $profileCreateData = $assembler->createDTO($userAdmin);

        $profileCreateData->setUsername(static::DEFAULT_ADMIN_USERNAME);
        $profileCreateData->setPlainPassword(static::DEFAULT_ADMIN_PASSWORD);
        $profileCreateData->setEmail('testuser+datahub@inuits.eu');
        $profileCreateData->setRoles(['ROLE_ADMIN']);

        $userAdmin = $assembler->updateProfile($userAdmin, $profileCreateData);

        $manager->persist($userAdmin);
        $manager->flush();

        $this->addReference('admin-user', $userAdmin);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
