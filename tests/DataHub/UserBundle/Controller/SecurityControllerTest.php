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
class SecurityControllerTest extends WebTestCase {

    public function testLoginForm() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/login');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Username', $response->getContent());

        $form = $crawler->selectButton('Login')->form();

        $form['login_form[_username]'] = 'admin';
        $form['login_form[_password]'] = 'datahub';

        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertRegexp(
            '/Logout/',
            $client->getResponse()->getContent()
        );
    }

    public function testLoginFormInvalidUsername() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/login');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Username', $response->getContent());

        $form = $crawler->selectButton('Login')->form();

        $form['login_form[_username]'] = 'invaliduser';
        $form['login_form[_password]'] = 'datahub';

        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertRegexp(
            '/Username could not be found./',
            $client->getResponse()->getContent()
        );
    }

    public function testLoginFormInvalidPassword() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/login');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Username', $response->getContent());

        $form = $crawler->selectButton('Login')->form();

        $form['login_form[_username]'] = 'admin';
        $form['login_form[_password]'] = 'invalidpassword';

        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertRegexp(
            '/Invalid credentials./',
            $client->getResponse()->getContent()
        );
    }

    public function testLoginFormEmptyFields() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/login');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Username', $response->getContent());

        $form = $crawler->selectButton('Login')->form();

        $form['login_form[_username]'] = '';
        $form['login_form[_password]'] = '';

        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertRegexp(
            '/Username could not be found./',
            $client->getResponse()->getContent()
        );
    }
}
