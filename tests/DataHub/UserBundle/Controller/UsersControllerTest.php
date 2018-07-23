<?php

namespace DataHub\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

use DataHub\OAIBundle\Repository\Repository;

/**
 * Functional testing for UsersController
 *
 * Functional testing suite for the Datahub Users section.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\UserBundle
 */
class UsersControllerTest extends WebTestCase {

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'datahub',
        ));
        $this->client->followRedirects(true);
        $this->client->setMaxRedirects(10);
    }

    public function testIndex() {
        $this->client->request('GET', '/user');

        $response = $this->client->getResponse();

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);
    }

    public function testShowUser() {
        // To be implemented
    }

    public function testAddNewUser() {
        // To be implemented
    }

    public function testDeleteUser() {
        // To be implemented
    }

    public function testUpdateValidPassowrd() {
        // To be implemented
    }

    public function testUpdateInvalidPassword() {
        // To be implemented
    }

    public function testLoginDisabledUser() {
        // To be implemented
    }

    public function testLoginEnabledUser() {
        // To be implemented
    }
}
