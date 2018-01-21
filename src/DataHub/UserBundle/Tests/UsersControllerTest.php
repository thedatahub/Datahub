<?php

namespace DataHub\UserBundle\Tests;

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
        $this->client = static::createClient();
    }

	public function testIndex() {
		$response = $this->client->request('GET', '/users', array(), array(), array(
		    'PHP_AUTH_USER' => 'admin',
		    'PHP_AUTH_PW'   => 'datahub',
		));

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);
	}

}