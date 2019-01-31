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
class SecurityControllerTest extends WebTestCase {

    public function setUp()
    {
        $fixtures = array(
           'DataHub\UserBundle\DataFixtures\MongoDB\LoadUserData',
        );
        $this->loadFixtures($fixtures, null, 'doctrine_mongodb');
    }

    public function testLoginForm() {
        $client = $this->makeClient();

        // Dashboard page
        $crawler = $client->request('GET', '/');
        $this->assertStatusCode(200, $client);
        $this->assertSame(1, $crawler->filter('a.login')->count());

        // Go to /user/login page
        $link = $crawler->filter('a[class="login"]')->link();
        $client->click($link);
        $this->assertStatusCode(200, $client);
        $crawler = $client->getCrawler();

        $this->assertContains('Log in to this Datahub', $client->getResponse()->getContent());
        $this->assertSame(1, $crawler->filter('button.login')->count());
        $this->assertSame(1, $crawler->filter('a.password-reset')->count());

        // Submit the form with the correct credentials
        $form = $crawler->selectButton('Login')->form();
        $form->setValues(
            array(
                'login_form[_username]' => 'admin',
                'login_form[_password]' => 'datahub',
            )
        );
        $client->submit($form);

        // Check if we are back on the dashboard and logged in as 'admin'.
        $client->followRedirect();
        $this->assertStatusCode(200, $client);
        // $this->assertTrue($client->getResponse()->isRedirect('/'));

        $crawler = $client->getCrawler();
        $this->assertSame(1, $crawler->filter('a.logout')->count());
        $this->assertSame(1, $crawler->filter('a.logged-in-user')->count());
        $this->assertSame('admin', $crawler->filter('a.logged-in-user')->text());

        // Go back to the /user/login page. Should redirect back to dashboard

        $client->request('GET', '/user/login');
        $this->assertStatusCode(302, $client);
        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $this->assertSame(1, $crawler->filter('a.logout')->count());
        $this->assertSame(1, $crawler->filter('a.logged-in-user')->count());
        $this->assertSame('admin', $crawler->filter('a.logged-in-user')->text());
    }

    public function testLoginFormInvalidCredentials() {
        $client = $this->makeClient();

        // Go directly to the /user/login page
        $crawler = $client->request('GET', '/user/login');
        $this->assertStatusCode(200, $client);

        // Submit the form with an incorrect username
        $form = $crawler->selectButton('Login')->form();
        $form->setValues(
            array(
                'login_form[_username]' => 'invaliduser',
                'login_form[_password]' => 'datahub',
            )
        );
        $client->submit($form);
   
        $client->followRedirect();
        $this->assertStatusCode(200, $client);
        $crawler = $client->getCrawler();

        $this->assertSame(1, $crawler->filter('div.alert-danger')->count());
        $this->assertSame('Those credentials are not valid.', $crawler->filter('div.alert-danger > strong')->text());

        // Submit the form with an incorrect password
        $form = $crawler->selectButton('Login')->form();
        $form->setValues(
            array(
                'login_form[_username]' => 'admin',
                'login_form[_password]' => 'invalidpassword',
            )
        );
        $client->submit($form);
   
        $client->followRedirect();
        $this->assertStatusCode(200, $client);
        $crawler = $client->getCrawler();

        $this->assertSame(1, $crawler->filter('div.alert-danger')->count());
        $this->assertSame('Those credentials are not valid.', $crawler->filter('div.alert-danger > strong')->text());
    }
   
    public function testLoginFormEmptyFields() {
        $client = $this->makeClient();

        // Go directly to the /user/login page
        $crawler = $client->request('GET', '/user/login');
        $this->assertStatusCode(200, $client);

        // Submit the form with an incorrect username
        $form = $crawler->selectButton('Login')->form();
        $form->setValues(
            array(
                'login_form[_username]' => '',
                'login_form[_password]' => '',
            )
        );
        $client->submit($form);
   
        $client->followRedirect();
        $this->assertStatusCode(200, $client);
        $crawler = $client->getCrawler();

        $this->assertSame(1, $crawler->filter('div.alert-danger')->count());
        $this->assertSame('Those credentials are not valid.', $crawler->filter('div.alert-danger > strong')->text());
    }

    public function testLoginFormInactiveUser()
    {
        $client = $this->makeClient();

        // Go directly to the /user/login page
        $crawler = $client->request('GET', '/user/login');
        $this->assertStatusCode(200, $client);

        // Submit the form with an inactive account
        $form = $crawler->selectButton('Login')->form();
        $form->setValues(
            array(
                'login_form[_username]' => 'inactiveconsumer',
                'login_form[_password]' => 'inactiveconsumer',
            )
        );
        $client->submit($form);
   
        $client->followRedirect();
        $this->assertStatusCode(200, $client);
        $crawler = $client->getCrawler();

        $this->assertSame(1, $crawler->filter('div.alert-danger')->count());
        $this->assertSame('Your account is inactive and needs be activated.', $crawler->filter('div.alert-danger > strong')->text());
    }
}
