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

        // @todo
        //  Test for better validation

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

        $link = $crawler->filter('table.oauth-clients tbody tr td.applicationname a')->last()->link();
        $client->click($link);
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();
        $this->assertContains('OAuth client: foobar', $client->getResponse()->getContent());
     
        $value = $crawler->filter('table tr.applicationname td')->first()->text();
        $this->assertSame('foobar', $value);
        $value = $crawler->filter('table tr.owner td')->first()->text();
        $this->assertSame('consumer', trim($value));
        $value = $crawler->filter('table tr.allowedgranttypes td')->first()->text();
        $this->assertSame('client_credentials', $value);
        $value = $crawler->filter('table tr.redirecturis td')->first()->text();
        $this->assertSame('', $value);
        $value = $crawler->filter('table tr.publicid td')->first()->count();
        $this->assertSame(1, $value);
        $value = $crawler->filter('table tr.secret td')->first()->count();
        $this->assertSame(1, $value);

        $this->assertSame(1, $crawler->filter('a.oauth-edit-client')->count());
        $this->assertSame(1, $crawler->filter('a.oauth-delete-client')->count());
        $this->assertSame(1, $crawler->filter('a.oauth-revoke-tokens')->count());

        // Edit a client

        $link = $crawler->filter('a.oauth-edit-client')->last()->link();
        $client->click($link);
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();
        $this->assertContains('Edit OAuth client', $client->getResponse()->getContent());

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Update client')->form();
        $form->setValues(
            array(
                'client_edit_form[applicationName]' => 'barfoo',
                'client_edit_form[redirectUris]' => '',
                'client_edit_form[allowedGrantTypes]' => array('client_credentials')
            )
        );
        $client->submit($form);

        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        $value = $crawler->filter('div.alert-success')->first()->text();
        $this->assertSame('OAuth client barfoo was successfully updated.', trim($value));

        $value = $crawler->filter('table tr.applicationname td')->first()->text();
        $this->assertSame('barfoo', $value);

        // Delete a client

        $link = $crawler->filter('a.oauth-delete-client')->first()->link();
        $client->click($link);
        $crawler = $client->getCrawler();

        $this->assertStatusCode(200, $client);
        $this->assertContains('Delete OAuth client', $client->getResponse()->getContent());

        $form = $crawler->selectButton('Cancel action')->form();
        $client->submit($form);

        // Back to the client profile
        $client->followRedirect();
        $this->assertStatusCode(200, $client);
        $crawler = $client->getCrawler();

        $link = $crawler->filter('a.oauth-delete-client')->first()->link();
        $client->click($link);
        $crawler = $client->getCrawler();

        $this->assertStatusCode(200, $client);
        $this->assertContains('Delete OAuth client', $client->getResponse()->getContent());

        $form = $crawler->selectButton('Delete this client')->form();
        $client->submit($form);

        // Back to the user profile
        $client->followRedirect();
        $this->assertStatusCode(200, $client);
        $this->assertContains('Profile: consumer', $client->getResponse()->getContent());

        $crawler = $client->getCrawler();
        $value = $crawler->filter('div.alert-success')->first()->text();
        $this->assertSame('Client barfoo removed successfully.', trim($value));
        $value = $crawler->filter('table.oauth-clients tbody tr')->first()->text();
        $this->assertSame('No results found.', trim($value));
    }
    
    public function testManageClientsAsAdministrator()
    {
        // @todo
        //   Implement me
    }

    public function testRevokeClientTokensAsConsumer()
    {
        // @todo
        //   Implement me        
    }
}