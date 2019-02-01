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
class ResettingControllerTest extends WebTestCase {

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

    public function testRegisterRedirectToInstaller()
    {
        $client = $this->makeClient();

        // Go to the login page

        $crawler = $client->request('GET', '/');
        $this->assertStatusCode(200, $client);

        $link = $crawler->filter('a[class="login"]')->link();
        $client->click($link);
        $this->assertStatusCode(200, $client);
        
        $crawler = $client->getCrawler();

        // Click on the Request reset password link

        $this->assertSame(1, $crawler->filter('a.password-reset')->count());

        $link = $crawler->filter('a.password-reset')->link();
        $client->click($link);
        $this->assertStatusCode(200, $client);

        // Check if the page works, and the form, email validation,...

        $crawler = $client->getCrawler();

        $this->assertContains('Reset your password', $client->getResponse()->getContent());

        // Check validation of the form
        // @todo make sure we don't give an indication about db state (user exists or not)

        $client->enableProfiler();

        $form = $crawler->selectButton('Request a new password.')->form();
        $form->setValues(
            array(
                'request_password_form[email]' => 'foo@bar.foo',
            )
        );
        $client->submit($form);

        $crawler = $client->getCrawler();

        $value = $crawler->filter('div.alert-success')->first()->text();
        $this->assertSame('An email with a reset link has been sent to foo@bar.foo', trim($value));

        // Check if a message was sent with a confirmation link.

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $this->assertSame(1, $mailCollector->getMessageCount());

        // Test the e-mail itself

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // @todo
        //   The e-mail should be send out in HTML, not plain text

        // Test the confirmation URL
        
        $regex = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
        preg_match_all($regex, $message->getBody(), $matches);
        $match = array_shift($matches);
        $url = array_shift($match);

        $this->assertRegExp('/http:\/\/localhost\/user\/resetting\/.*/', $url);  

        // Reset the password in the edit an existing user form.

        $client->request('GET', $url);
        $this->assertStatusCode(302, $client);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        $this->assertSame(1, $crawler->filter('a.logout')->count());
        $this->assertSame(1, $crawler->filter('a.logged-in-user')->count());
        $this->assertSame('admin', $crawler->filter('a.logged-in-user')->text());

        $form = $crawler->selectButton('Update this user')->form();
        $form->setValues(
            array(
                'profile_edit_form[plainPassword][first]' => 'Foob4r!',
                'profile_edit_form[plainPassword][second]' => 'Foob4r!',
            )
        );
        $client->submit($form);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        $value = $crawler->filter('div.alert-success')->first()->text();
        $this->assertSame('User admin was edited successfully.', trim($value));

        // Log out and log back in with the updated password.

        $link = $crawler->filter('a[class="logout"]')->link();
        $client->click($link);
        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->request('GET', '/');
        $link = $crawler->filter('a[class="login"]')->link();
        $client->click($link);
        $crawler = $client->getCrawler();

        $form = $crawler->selectButton('Login')->form();
        $form->setValues(
            array(
                'login_form[_username]' => 'admin',
                'login_form[_password]' => 'Foob4r!',
            )
        );
        $client->submit($form);

        // Check if we are back on the dashboard and logged in as 'admin'.
        
        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();
        $this->assertSame(1, $crawler->filter('a.logout')->count());
        $this->assertSame(1, $crawler->filter('a.logged-in-user')->count());
        $this->assertSame('admin', $crawler->filter('a.logged-in-user')->text());

        // Let's log out and try the confirmation token again. Shouldn't work this time.

        $link = $crawler->filter('a[class="logout"]')->link();
        $client->click($link);
        $this->assertStatusCode(302, $client);
        $client->followRedirect();
        $this->assertStatusCode(200, $client);
        $crawler = $client->getCrawler();

        $this->assertSame(1, $crawler->filter('a.login')->count());

        $client->request('GET', $url);
        $this->assertStatusCode(302, $client);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        $this->assertContains('Log in to this Datahub', $client->getResponse()->getContent());
        $this->assertSame(1, $crawler->filter('button.login')->count());
        $this->assertSame(1, $crawler->filter('a.password-reset')->count());
    }
}
