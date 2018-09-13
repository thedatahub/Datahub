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
        $userAdmin = new User();
        $userAdmin->setUsername(static::DEFAULT_ADMIN_USERNAME);
        $userAdmin->setPlainPassword(static::DEFAULT_ADMIN_PASSWORD);
        $userAdmin->setEmail('testuser+datahub@inuits.eu');
        $userAdmin->setEnabled(true);
        $userAdmin->setRoles(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);

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
