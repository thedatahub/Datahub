<?php

namespace DataHub\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use DataHub\OAIBundle\Repository\Repository;

/**
 * Functional testing for AdminController
 *
 * Functional testing suite for the Datahub Users section.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\UserBundle
 */
class AdminControllerTest extends WebTestCase 
{
    public function testIndexActionAsSuperAdmin()
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->setMaxRedirects(10);

        $crawler = $client->request('GET', '/user/login');
        $form = $crawler->selectButton('Login')->form();
        $form['login_form[_username]'] = 'admin';
        $form['login_form[_password]'] = 'datahub';
        $client->submit($form);

        $crawler = $client->request('GET', '/user/users');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Username', $response->getContent());

        // I can add a new user

        $link = $crawler->filter('a[href="/user/add"]')->link();
        $client->click($link);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $crawler = $client->getCrawler();
        $this->assertSame('Add new user', trim($crawler->filter('div.panel-primary h3[class="panel-title"]')->text()));

        // I can see an existing user

        $crawler = $client->request('GET', '/user/users');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Username', $response->getContent());

        $this->assertSame('admin', trim($crawler->filter('table.users td.username a')->text()));
   
        // I can edit the user

        $nodes = $crawler->filter('table.users td.actions div > a');
        $this->assertSame('edit', trim($nodes->first()->text()));

        $link = $nodes->first()->link();
        $client->click($link);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // I can delete the user

        $this->assertSame('delete', trim($nodes->last()->text()));

        $link = $nodes->last()->link();
        $client->click($link);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexActionAsAnonymous()
    {
        // I can't see the page (AccessDeniedException).
        // I'm redirected to the login page

        $client = static::createClient();
        $client->followRedirects(true);
        $client->setMaxRedirects(10);

        $client->request('GET', '/user/logout');

        $crawler = $client->request('GET', '/user/users');

        $this->assertRegExp('/\/user\/login$/', $client->getHistory()->current()->getUri());
    }
}
