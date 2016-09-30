<?php

namespace VKC\DataHub\OAuthBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

use VKC\DataHub\OAuthBundle\DataFixtures\MongoDB\LoadClientData;
use VKC\DataHub\UserBundle\DataFixtures\MongoDB\LoadUserData;


/**
 * Basic OAuth2-enabled functional test.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class OAuthTestCase extends WebTestCase
{
    const OAUTH2_TOKEN_URL = '/oauth/v2/token';
    const API_URI_PREFIX = '/api';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
    */
    protected $refreshToken;

    /**
     * Get the client.
     *
     * @return Client
     */
    protected function getClient()
    {
        ini_set('memory_limit', '-1');

        if (!$this->client) {
            $this->client = static::createClient();
        }

        return $this->client;
    }

    /**
     * Get the access token.
     *
     * @return string
     */
    protected function getAccessToken()
    {
        if (!$this->accessToken) {
            $client = $this->getClient();

            $crawler = $client->request('GET', static::OAUTH2_TOKEN_URL, array(
                'client_id'     => LoadClientData::DEFAULT_CLIENT_PUBLIC_ID,
                'client_secret' => LoadClientData::DEFAULT_CLIENT_SECRET_ID,
                'username'      => LoadUserData::DEFAULT_ADMIN_USERNAME,
                'password'      => LoadUserData::DEFAULT_ADMIN_PASSWORD,
                'grant_type'    => 'password',
            ));

            $response = $client->getResponse();
            $this->assertTrue($response->isSuccessful());

            $data = json_decode($response->getContent(), true);

            $this->assertTrue(is_array($data));
            $this->assertArrayHasKey('access_token', $data);
            $this->assertArrayHasKey('refresh_token', $data);

            $this->accessToken = $data['access_token'];
            $this->refreshToken = $data['refresh_token'];

        }

        return $this->accessToken;
    }

    /**
     * Perform a request to the API.
     *
     * @param  string  $method          [description]
     * @param  string  $uri             [description]
     * @param  array   $jsonData        [description]
     * @param  array   $queryParameters [description]
     * @param  array   $parameters      [description]
     * @param  array   $files           [description]
     * @param  array   $server          [description]
     * @param  [type]  $content         [description]
     * @param  boolean $changeHistory   [description]
     * @return [type]                   [description]
     */
    protected function apiRequest(
        $method,
        $uri,
        array $jsonData = array(),
        array $queryParameters = array(),
        array $parameters = array(),
        array $files = array(),
        array $server = array(),
        $content = null,
        $changeHistory = true
    ) {
        $client = $this->getClient();
        $accessToken = $this->getAccessToken();

        $query = http_build_query(array_merge(array(
            'access_token' => $accessToken,
        ), $queryParameters));
        $query = preg_replace('/\[\d+\]/', '[]', $query);
        $query = urldecode($query);

        $uri = static::API_URI_PREFIX . $uri . '?' . $query;

        $server = array_merge(array(
            'CONTENT_TYPE' => 'application/json',
        ), $server);

        $content = $content ? $content : (empty($jsonData) ? $content : json_encode($jsonData));

        $crawler = $client->request(
            $method,
            $uri,
            $parameters,
            $files,
            $server,
            $content,
            $changeHistory
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        return array($crawler, $response, $data);
    }

    /**
     * Assert the given data contains the required keys
     * expected of a CRUD listing.
     *
     * @param  array $data
     * @return void
     */
    protected function assertCRUDListContent($data)
    {
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('results', $data);
        $this->assertGreaterThanOrEqual($data['count'], count($data['results']));
    }
}
