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
class ClientControllerTest extends WebTestCase {

    public function setUp()
    {
        $this->loadFixtures(
            array(
                'DataHub\UserBundle\DataFixtures\MongoDB\LoadUserData'
            ), 
            null, 
            'doctrine_mongodb'
        );
    }

    public function testManageClientsAsConsumer()
    {
        $client = $this->makeClient();

        // Log in as an administrator
        $crawler = $client->request('GET', '/');
        $link = $crawler->filter('a[class="login"]')->link();
        $client->click($link);
        
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Login')->form();
        $form->setValues(
            array(
                'login_form[_username]' => 'consumer',
                'login_form[_password]' => 'consumer',
            )
        );
        $client->submit($form);
        $client->followRedirect();

        // Go to the 'profile' page.

        $crawler = $client->getCrawler();
        $link = $crawler->filter('a.logged-in-user')->link();
        $client->click($link);

        $this->assertStatusCode(200, $client);

        // Check if the 'OAuth Clients' table is there

        $crawler = $client->getCrawler();
        $this->assertSame(1, $crawler->filter('table.oauth-clients')->count());
        $this->assertSame(1, $crawler->filter('a.oauth-clients-add-client')->count());

        // Adda new OAuth client

        $link = $crawler->filter('a.oauth-clients-add-client')->link();
        $client->click($link);
        
        // validation

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('New client')->form();
        $form->setValues(
            array(
                'client_create_form[applicationName]' => '',
                'client_create_form[redirectUris]' => '',
                'client_create_form[allowedGrantTypes]' => array()
            )
        );
        $client->submit($form);

        $crawler = $client->getCrawler();
        
        $value = $crawler->filter('div.form-group-applicationname span.help-block ul li')->first()->text();
        $this->assertSame(' This value should not be blank.', $value);

        $value = $crawler->filter('div.form-group-allowedgranttypes span.help-block ul li')->first()->text();
        $this->assertSame(' This value is not valid.', $value);

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('New client')->form();
        $form->setValues(
            array(
                'client_create_form[applicationName]' => 'foobar',
                'client_create_form[redirectUris]' => '',
                'client_create_form[allowedGrantTypes]' => array('client_credentials')
            )
        );
        $client->submit($form);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        // Check if the OAuth client has been created

        $value = $crawler->filter('div.alert-success')->first()->text();
        $this->assertSame('OAuth client foobar created successfully.', trim($value));
        $this->assertSame(1, $crawler->filter('table.oauth-clients tbody tr')->count());
        $value = $crawler->filter('table.oauth-clients tbody tr td.applicationname a')->last()->text();
        $this->assertSame('foobar', trim($value));
        $value = $crawler->filter('table.oauth-clients tbody tr td.allowedgranttypes')->last()->text();
        $this->assertSame('client_credentials', trim($value));
        $value = $crawler->filter('table.oauth-clients tbody tr td.actions a.oauth-clients-edit-client')->last()->text();
        $this->assertSame('Edit', trim($value));
        $value = $crawler->filter('table.oauth-clients tbody tr td.actions a.oauth-clients-delete-client')->last()->text();
        $this->assertSame('Delete', trim($value));

        // Go to the detail page of the OAuth client

        
    }
}