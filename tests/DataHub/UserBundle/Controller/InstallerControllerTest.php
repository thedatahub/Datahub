<?php

namespace DataHub\UserBundle\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Functional testing for InstallerController
 *
 * Functional testing suite for the Datahub Users section.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\UserBundle
 */
class InstallerControllerTest extends WebTestCase {

    public function setUp()
    {
        $this->loadFixtures(array(), null, 'doctrine_mongodb');
    }

    public function testFirstTimeInstallation()
    {
        $client = $this->makeClient();

        // Go to the dashboard page
        $client->request('GET', '/');
        $this->assertStatusCode(302, $client);

        // Check if redirected to /user/install/administrator
        $this->assertTrue($client->getResponse()->isRedirect('/user/install/administrator'));

        // Check if the form is rendered correctly
        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        $this->assertContains('Create an administrative user', $client->getResponse()->getContent());
        $this->assertSame(1, $crawler->filter('form[name="installer_create_form"]')->count());

        // Check field validation

        $form = $crawler->selectButton('Create administrator')->form();
        $form->setValues(
            array(
                'installer_create_form[username]' => '',
                'installer_create_form[firstName]' => '',
                'installer_create_form[lastName]' => '',
                'installer_create_form[email]' => ''
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

        $form = $crawler->selectButton('Create administrator')->form();
        $form->setValues(
            array(
                'installer_create_form[username]' => 'admin',
                'installer_create_form[firstName]' => 'Foo',
                'installer_create_form[lastName]' => 'Bar',
                'installer_create_form[email]' => 'invalidemail'
            )
        );
        $client->submit($form);

        $crawler = $client->getCrawler();

        $value = $crawler->filter('div.form-group-email span.help-block ul li')->first()->text();
        $this->assertSame(' This value is not a valid email address.', $value);

        // Proceed with a valid administrator user

        $client->enableProfiler();
        $crawler = $client->getCrawler();

        $form = $crawler->selectButton('Create administrator')->form();
        $form->setValues(
            array(
                'installer_create_form[username]' => 'admin',
                'installer_create_form[firstName]' => 'Foo',
                'installer_create_form[lastName]' => 'Bar',
                'installer_create_form[email]' => 'foo@bar.barfoo'
            )
        );
        $client->submit($form);

        // Check if the mail is succesfully send out (catch it)

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

        $this->assertRegExp('/http:\/\/localhost\/user\/registration\/confirmation\/.*/', $url);
        
        // Navigate back to the dashboard page with a notification

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        $value = $crawler->filter('div.alert-success')->first()->text();
        $this->assertSame('Superadministrator admin created successfully. An email was send to foo@bar.barfoo', trim($value));

        // Let's confirm the new superadministrator and make sure we get logged in.

        $client->request('GET', $url);
        $this->assertStatusCode(302, $client);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

        $crawler = $client->getCrawler();

        $this->assertSame(1, $crawler->filter('a.logout')->count());
        $this->assertSame(1, $crawler->filter('a.logged-in-user')->count());
        $this->assertSame('admin', $crawler->filter('a.logged-in-user')->text());

        //   We should have a nice registration confirmed message
        $this->assertContains('<p>Congrats <strong>admin</strong>, your account is now activated.</p>', $client->getResponse()->getContent());

        // Click on the 'Continue' button
        $link = $crawler->filter('a.confirmed-continue')->link();
        $client->click($link);
        $this->assertStatusCode(200, $client);
        $crawler = $client->getCrawler();

        // Yup, I see the navbar, I'm not on the installer page anymore.
        // @todo
        //   This is bad design. The dashboard probably needs a proper heading,...
        $this->assertSame(1, $crawler->filter('a.logout')->count());
        $this->assertSame(1, $crawler->filter('a.logged-in-user')->count());
        $this->assertSame('admin', $crawler->filter('a.logged-in-user')->text());

        // The installer should now not be reachable anymore.
        $client->request('GET', '/user/install/administrator');
        $this->assertStatusCode(302, $client);

        $client->followRedirect();
        $this->assertStatusCode(200, $client);

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