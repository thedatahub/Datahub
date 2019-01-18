<?php

namespace DataHub\UserBundle\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Functional testing for UsersController
 *
 * Functional testing suite for the Datahub Users section.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\UserBundle
 */
class RegistrationControllerTest extends WebTestCase {

    public function setUp()
    {
        $this->loadFixtures(array(), null, 'doctrine_mongodb');
    }

    public function testRegisterRedirectToInstaller()
    {
        $client = $this->makeClient();

        // @todo
        //   Right now, this is wired to redirect to the installer, but if we
        //   want to add free registration (instead of forced registration), we'll need
        //   to rethink / refactor htis flow.
        $crawler = $client->request('GET', '/register');
        $this->assertStatusCode(302, $client);
        $this->assertTrue($client->getResponse()->isRedirect('/user/install/administrator'));
    }
}
