<?php

namespace DataHub\UserBundle\DataFixtures\MongoDB;

use DataHub\SharedBundle\DataFixtures\EnvironmentSpecificDataFixture as AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use DataHub\UserBundle\Document\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    const DEFAULT_ADMIN_USERNAME = 'admin';
    const DEFAULT_ADMIN_PASSWORD = 'datahub';

    /**
     * {@inheritDoc}
     */
    protected function doLoad(ObjectManager $manager)
    {
        // Administrator

        $userAdmin = new User;
        $assembler = $this->container->get('datahub.security.user.dto.profile_create_assembler');
        
        $profileCreateData = $assembler->createDTO($userAdmin);

        $profileCreateData->setUsername(static::DEFAULT_ADMIN_USERNAME);
        $profileCreateData->setFirstName('Foo');
        $profileCreateData->setLastName('Bar');
        $profileCreateData->setPlainPassword(static::DEFAULT_ADMIN_PASSWORD);
        $profileCreateData->setEmail('foo@bar.foo');
        $profileCreateData->setRoles(['ROLE_ADMIN']);

        $userAdmin = $assembler->updateProfile($userAdmin, $profileCreateData);

        $manager->persist($userAdmin);
        $manager->flush();

        $this->addReference('admin-user', $userAdmin);

        // Manager

        $userManager = new User;
        $assembler = $this->container->get('datahub.security.user.dto.profile_create_assembler');
        
        $profileCreateData = $assembler->createDTO($userManager);

        $profileCreateData->setUsername('manager');
        $profileCreateData->setFirstName('Manager');
        $profileCreateData->setLastName('Manager');
        $profileCreateData->setPlainPassword('manager');
        $profileCreateData->setEmail('foo.manager@bar.foo');
        $profileCreateData->setRoles(['ROLE_MANAGER']);

        $userManager = $assembler->updateProfile($userManager, $profileCreateData);

        $manager->persist($userManager);
        $manager->flush();

        $this->addReference('manager-user', $userManager);

        // Consumer

        $userConsumer = new User;
        $assembler = $this->container->get('datahub.security.user.dto.profile_create_assembler');
        
        $profileCreateData = $assembler->createDTO($userConsumer);

        $profileCreateData->setUsername('consumer');
        $profileCreateData->setFirstName('Consumer');
        $profileCreateData->setLastName('Consumer');
        $profileCreateData->setPlainPassword('consumer');
        $profileCreateData->setEmail('foo.consumer@bar.foo');
        $profileCreateData->setRoles(['ROLE_CONSUMER']);

        $userConsumer = $assembler->updateProfile($userConsumer, $profileCreateData);

        $manager->persist($userConsumer);
        $manager->flush();

        $this->addReference('consumer-user', $userConsumer);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
