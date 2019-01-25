<?php

namespace DataHub\ResourceAPIBundle\Controller;

# use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * Functional testing for RecordController
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceAPIBundle
 */
class RecordControllerTest extends WebTestCase
{
    private $record;

    private $dataPid;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->loadFixtures(
            array(
                'DataHub\UserBundle\DataFixtures\MongoDB\LoadUserData',
                'DataHub\OAuthBundle\DataFixtures\MongoDB\LoadClientData',
                'DataHub\ResourceAPIBundle\DataFixtures\MongoDB\LoadRecordData'
            ), 
            null, 
            'doctrine_mongodb'
        );

        $this->validRecord = file_get_contents(__DIR__.'/../../Resources/lido.xml');
        $this->invalidRecord = file_get_contents(__DIR__.'/../../Resources/lido_invalid.xml');
        $this->jsonRecord = file_get_contents(__DIR__.'/../../Resources/lido.json');
        $this->emptyRecord = '';
        $this->dataPid = 'DE-Mb112/lido-obj00154983';
    }

    /**
     * Gets an OAuth access token
     *
     * @return string A valid OAuth access token.
     */
    protected function getAccessToken() {
        $client = $this->makeClient();
        $client->request('GET', '/oauth/v2/token?grant_type=client_credentials&username=admin&password=datahub&client_id=slightlylesssecretpublicid&client_secret=supersecretsecretphrase');
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        return $data['access_token'];
    }

    /**
     * Implements a GET client call.
     *
     * @param string $id Optional identifier for a record. If not specified, all
     *        all records will be retrieved.
     *
     * @param Symfony\Component\HttpFoundation\Response Response object
     */
    protected function get($id = null, $format = 'json') {
        $format = ($format == 'json') ? '' : '.xml';
        $accessToken = $this->getAccessToken();
        $action = (!is_null($id)) ? sprintf("/api/v1/data/%s%s", urlencode($id), $format) : "/api/v1/data";
        $action = sprintf('%s?access_token=%s', $action, $accessToken);

        $client = $this->makeClient();
        $client->request('GET', $action);

        return $client->getResponse();
    }

    /**
     * Implements a GET client call.
     *
     * @param string $limit
     * @param string $offset
     *
     * @param Symfony\Component\HttpFoundation\Response Response object
     */
    protected function getAll($limit = null, $offset = null) {
        $accessToken = $this->getAccessToken();
        if (!is_null($limit) && !is_null($offset)) {
            $action = sprintf("/api/v1/data?offset=%s&limit=%s", $offset, $limit);
        } else {
            $action = '/api/v1/data';
        }

        $action = sprintf('%s?access_token=%s', $action, $accessToken);

        $client = $this->makeClient();
        $client->request('GET', $action);

        return $client->getResponse();
    }

    /**
     * Implements a POST client call.
     *
     * @param string $record Required valid record string.
     *
     * @param Symfony\Component\HttpFoundation\Response Response object
     */
    protected function post($record) {
        $accessToken = $this->getAccessToken();
        $action = sprintf('/api/v1/data?access_token=%s', $accessToken);

        $client = $this->makeClient();
        $client->request('POST', $action, [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $record);

        return $client->getResponse();
    }

    /**
     * Implements a PUT client call.
     *
     * @param string $id     The identifier of the record to be updated or created.
     * @param string $record Required valid record string.
     *
     * @param Symfony\Component\HttpFoundation\Response Response object
     */
    protected function put($id, $record) {
        $accessToken = $this->getAccessToken();
        $action = sprintf('/api/v1/data/%s?access_token=%s', $id, $accessToken);

        $client = $this->makeClient();
        $client->request('PUT', $action, [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $record);

        return $client->getResponse();
    }

    /**
     * Implements a DELETE client call.
     *
     * @param string $id     The identifier of the record to be deleted.
     * @param string $record Required valid record string.
     *
     * @param Symfony\Component\HttpFoundation\Response Response object
     */
    protected function delete($id) {
        $accessToken = $this->getAccessToken();
        $action = sprintf("/api/v1/data/%s?access_token=%s", $id, $accessToken);

        $client = $this->makeClient();
        $client->request('DELETE', $action);

        return $client->getResponse();
    }

    public function testPostAction()
    {
        $response = $this->post($this->validRecord);
        
        // Test valid record

        $statusCode = $response->getStatusCode();
        $location = $response->headers->get('location');
        $dataPid = urldecode(preg_replace('/\/api\/v1\/data\/(.*)$/', '$1', $location));

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(201, $statusCode);
        $this->assertEquals($this->dataPid, $dataPid);

        // Test duplicate record

        $response = $this->post($this->validRecord);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = json_decode($content, true);
    
        $this->assertEquals(409, $statusCode);
        $this->assertEquals("Record with this ID already exists.", $content['message']);

        // Test invalid record

        $response = $this->post($this->invalidRecord);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = json_decode($content, true);
    
        $this->assertEquals(400, $statusCode);
        $this->assertEquals("Invalid XML: Element '{http://www.lido-schema.org}category': This element is not expected. Expected is ( {http://www.lido-schema.org}lidoRecID ).\n on line 3, column 0", $content['message']);

        // Test empty record

        $response = $this->post($this->emptyRecord);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(422, $statusCode);
        $this->assertEquals("No record was provided.", $content['message']);
    }

    public function testPutAction() {

        // Test a valid record

        $response = $this->put($this->dataPid, $this->validRecord);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(201, $statusCode);
        $this->assertEmpty($content);

        // Update a valid record

        $response = $this->put($this->dataPid, $this->validRecord);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(204, $statusCode);
        $this->assertEmpty($content);

        // Create an invalid record

        $response = $this->put($this->dataPid, $this->invalidRecord);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(400, $statusCode);
        $this->assertEquals("Invalid XML: Element '{http://www.lido-schema.org}category': This element is not expected. Expected is ( {http://www.lido-schema.org}lidoRecID ).\n on line 3, column 0", $content['message']);

        // Create an empty record

        $response = $this->put($this->dataPid, $this->emptyRecord);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(422, $statusCode);
        $this->assertEquals("No record was provided.", $content['message']);
    }

    public function testDeleteAction() {

        // Delete an existing record 

        $this->post($this->validRecord);

        $response = $this->delete($this->dataPid);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(204, $statusCode);
        $this->assertEmpty($content);

        // Delete a non-existing record 

        $response = $this->delete($this->dataPid);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(404, $statusCode);
        $this->assertEquals("Record could not be found.", $content['message']);
    }

    public function testGetSingleRecordAction()
    {
        $this->post($this->validRecord);

        // Test the JSON variant

        $response = $this->get($this->dataPid);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);
        $this->assertJsonStringEqualsJsonString($this->jsonRecord, $content);

        // Test the XML variant

        $response = $this->get($this->dataPid, 'xml');
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);
        $this->assertXMLStringEqualsXMLString($this->validRecord, $content);
    }

    public function testGetMultipleRecordsAction() 
    {
        $response = $this->getAll();
        $statusCode = $response->getStatusCode();

        $this->assertEquals(200, $statusCode);

        $content = $response->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(0, $content['offset']);
        $this->assertEquals(5, $content['limit']);
        $this->assertEquals(15, $content['total']);
        $this->assertEquals($content['_links'], [
            'self' => [ 'href' => '/api/v1/data?limit=5'],
            'first' => [ 'href' => '/api/v1/data?limit=5'],
            'last' => [ 'href' => '/api/v1/data?offset=10&limit=5' ],
            'next' => [ 'href' => '/api/v1/data?offset=5&limit=5' ]
        ]);

        $response = $this->getAll(5, 5);
        $statusCode = $response->getStatusCode();

        $this->assertEquals(200, $statusCode);

        $content = $response->getContent();
        $content = json_decode($content, true);

        $this->assertEquals(5, $content['offset']);
        $this->assertEquals(5, $content['limit']);
        $this->assertEquals(15, $content['total']);
        $this->assertEquals($content['_links'], [
            'self' => [ 'href' => '/api/v1/data?offset=5&limit=5'],
            'first' => [ 'href' => '/api/v1/data?limit=5'],
            'last' => [ 'href' => '/api/v1/data?offset=10&limit=5' ],
            'next' => [ 'href' => '/api/v1/data?offset=10&limit=5' ],
            'previous' => [ 'href' => '/api/v1/data?limit=5' ]
        ]);
    }
}
