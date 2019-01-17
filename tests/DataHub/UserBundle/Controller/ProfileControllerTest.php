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
class ProfileControllerTest extends WebTestCase {

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

    public function testManageUsersAsSuperadmin()
    {
        $client = $this->makeClient();

        // Log in ad a superadmin
        $crawler = $client->request('GET', '/');
        $link = $crawler->filter('a[class="login"]')->link();
        $client->click($link);
        
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Login')->form();
        $form->setValues(
            array(
                'login_form[_username]' => 'admin',
                'login_form[_password]' => 'datahub',
            )
        );
        $client->submit($form);
        $client->followRedirect();

        // Go to the 'Administration' page.

        $crawler = $client->getCrawler();
        $link = $crawler->filter('a.admin-administration')->link();
        $client->click($link);

        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();
        $this->assertSame(1, $crawler->filter('table.users')->count());
        $this->assertSame(1, $crawler->filter('a.users-add-user')->count());

        // Add a new user
        
        $link = $crawler->filter('a.users-add-user')->link();
        $client->click($link);
        
        // @todo validation etc.
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('New user')->form();
        $form->setValues(
            array(
                'profile_create_form[username]' => '',
                'profile_create_form[firstName]' => '',
                'profile_create_form[lastName]' => '',
                'profile_create_form[email]' => '',
                'profile_create_form[plainPassword][first]' => '',
                'profile_create_form[plainPassword][second]' => '',
            )
        );
        $client->submit($form);

        $crawler = $client->getCrawler();
        
        $value = $crawler->filter('div.form-group-username span.help-block ul li')->first()->text();
        $this->assertSame(' This value should not be blank.', $value);

        $value = $crawler->filter('div.form-group-firstname span.help-block ul li')->first()->text();
        $this->assertSame(' This value should not be blank.', $value);

        $value = $crawler->filter('div.form-group-lastname span.help-block ul li')->first()->text();
        $this->assertSame(' This value should not be blank.', $value);

        $value = $crawler->filter('div.form-group-email span.help-block ul li')->first()->text();
        $this->assertSame(' This value should not be blank.', $value);

        // @todo
        //   This test is no good! Does it work without passwords??
        //   Should throw an error!!!
        // $crawler = $client->getCrawler();
        // $form = $crawler->selectButton('New user')->form();
        // $form->setValues(
        //     array(
        //         'profile_create_form[username]' => 'user',
        //         'profile_create_form[firstName]' => 'foo',
        //         'profile_create_form[lastName]' => 'bar',
        //         'profile_create_form[email]' => 'user@foo.barfoo',
        //         'profile_create_form[plainPassword][first]' => '',
        //         'profile_create_form[plainPassword][second]' => '',
        //     )
        // );
        // $client->submit($form);

        // @todo
        //   Test against re-using a username (unique username constraint!)

        // @todo
        //   Test against re-using an e-mail address (unique email constraint!)

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('New user')->form();
        $form->setValues(
            array(
                'profile_create_form[username]' => 'user',
                'profile_create_form[firstName]' => 'foo',
                'profile_create_form[lastName]' => 'bar',
                'profile_create_form[email]' => 'user@foo.barfoo',
                'profile_create_form[plainPassword][first]' => 'valid',
                'profile_create_form[plainPassword][second]' => 'invalid',
            )
        );
        $client->submit($form);

        // @todo
        //   Change the error message for inequal passwords
        $crawler = $client->getCrawler();
        $value = $crawler->filter('div.form-group-password div.has-error span.help-block ul li')->first()->text();
        $this->assertSame(' This value is not valid.', $value);

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('New user')->form();
        $form->setValues(
            array(
                'profile_create_form[username]' => 'user',
                'profile_create_form[firstName]' => 'foo',
                'profile_create_form[lastName]' => 'bar',
                'profile_create_form[email]' => 'user@foo.barfoo',
                'profile_create_form[plainPassword][first]' => 'Foob4r!',
                'profile_create_form[plainPassword][second]' => 'Foob4r!',
            )
        );
        $client->submit($form);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        $value = $crawler->filter('div.alert-success')->first()->text();
        $this->assertSame('User user created successfully.', trim($value));
        $this->assertSame(2, $crawler->filter('table.users tbody tr')->count());
        $value = $crawler->filter('table.users tbody tr td.username a')->last()->text();
        $this->assertSame('user', trim($value));
       
        // Show the profile of an user

        $link = $crawler->filter('table.users tbody tr td.username a')->last()->link();
        $client->click($link);
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();
        $this->assertContains('Profile: user', $client->getResponse()->getContent());

        $value = $crawler->filter('dl.user-profile dd.field-username')->first()->text();
        $this->assertSame('user', $value);
        $value = $crawler->filter('dl.user-profile dd.field-fullname')->first()->text();
        $this->assertSame('foo bar', $value);
        $value = $crawler->filter('dl.user-profile dd.field-email')->first()->text();
        $this->assertSame('user@foo.barfoo', $value);
        $value = $crawler->filter('dl.user-profile dd.field-roles')->first()->text();
        $this->assertSame('ROLE_CONSUMER', $value);

        $this->assertSame(1, $crawler->filter('a.user-edit-user')->count());
        $this->assertSame(1, $crawler->filter('a.user-delete-user')->count());

        // Edit an existing user

        $link = $crawler->filter('a.admin-administration')->link();
        $client->click($link);

        $crawler = $client->getCrawler();
        $link = $crawler->filter('table.users tbody tr td.actions a.users-edit-user')->last()->link();
        $client->click($link);
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();
        $this->assertContains('Edit an existing user', $client->getResponse()->getContent());

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Update user')->form();
        $form->setValues(
            array(
                'profile_edit_form[username]' => 'user',
                'profile_edit_form[firstName]' => 'bar',
                'profile_edit_form[lastName]' => 'foo',
                'profile_edit_form[email]' => 'user@foo.barfoo',
                'profile_edit_form[plainPassword][first]' => 'Foob4r!',
                'profile_edit_form[plainPassword][second]' => 'Foob4r!',
            )
        );
        $client->submit($form);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();
        $value = $crawler->filter('div.alert-success')->first()->text();
        $this->assertSame('User user was edited successfully.', trim($value));

        $value = $crawler->filter('dl.user-profile dd.field-fullname')->first()->text();
        $this->assertSame('bar foo', trim($value));

        // @todo edit a non-existing user

        // Delete an existing user

        $crawler = $client->getCrawler();
        $link = $crawler->filter('a.admin-administration')->link();
        $client->click($link);
        $crawler = $client->getCrawler();

        $link = $crawler->filter('table.users tbody tr td.actions a.users-delete-user')->last()->link();
        $client->click($link);

        $crawler = $client->getCrawler();
        $this->assertContains('Delete user', $client->getResponse()->getContent());

        $form = $crawler->selectButton('Cancel action')->form();
        $client->submit($form);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);
       
        $crawler = $client->getCrawler();
        $this->assertSame(2, $crawler->filter('table.users tbody tr')->count());

        $link = $crawler->filter('table.users tbody tr td.actions a.users-delete-user')->last()->link();
        $client->click($link);

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Delete this user')->form();
        $client->submit($form);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();
        $this->assertSame(1, $crawler->filter('table.users tbody tr')->count());

        // @todo delete a non-existing user
    }

    public function testManageUsersAsAnonymous()
    {
        // @todo
        //   Make sure anonoymous can't add, delete or edit users
    }

    public function testManageUsersAsUser()
    {
        // @todo
        //   Make sure user can't add, delete or edit other users
    }
}