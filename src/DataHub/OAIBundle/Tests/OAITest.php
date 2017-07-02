<?php

namespace DataHub\OAIBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

use DataHub\OAIBundle\Repository\Repository;


/**
 * Tests the OAI endpoint.
 */
class OAITestCase extends WebTestCase {

    /**
     * @var Client
     */
    private $client;

    /**
     * @var number of test records inserted
     */
    private $numTestRecords;

    /**
     * @var number of test records per page
     */
    private $paginationSize;

    const OAI_NS = '{http://www.openarchives.org/OAI/2.0/}';


    protected function setUp() {
        $this->client = static::createClient();
        $this->paginationSize = 5;
    }

    public function testIdentify() {
        $verb = 'Identify';
        $this->client->request('GET', '/oai/?verb='.$verb);
        $response = $this->client->getResponse()->getContent();
        $response = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $response = $response[$verb];
        $this->assertArrayHasKey('repositoryName', $response);
        $this->assertEquals('Datahub OAI', $response['repositoryName']);
    }

    public function testListMetadataFormats() {
        $verb = 'ListMetadataFormats';
        $this->client->request('GET', '/oai/?verb='.$verb);
        $response = $this->client->getResponse()->getContent();
        $response = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $response = $response[$verb];
        $metadataPrefixes = array_column($response['metadataFormat'], 'metadataPrefix');
        $this->assertContains('oai_lido', $metadataPrefixes);
    }

    public function testListSets() {
        $verb = 'ListSets';
        $this->client->request('GET', '/oai/?verb='.$verb);
        $response = $this->client->getResponse()->getContent();
        $response = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $response = $response[$verb];
        $this->assertArrayHasKey('set', $response);
        $this->assertArrayHasKey('setName', $response['set']);
        $this->assertEquals('All records', $response['set']['setName']);
        //TODO extend this test when we actually are going to use sets
    }

    public function loadTestDataIntoMongo($numRecords = 1) {
        // Retrieve access token
        $this->client->request('GET', '/oauth/v2/token?grant_type=password&username=admin&password=datahub&client_id=slightlylesssecretpublicid&client_secret=supersecretsecretphrase');
        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $data = json_decode($response->getContent(), true);
        $accessToken = $data['access_token'];

        // Insert $numRecord amount of files
        $testFiles = array_values(array_diff(scandir(__DIR__.'/../Resources/LidoXML/'), array('..', '.')));
        $i = 0;
        while ($i < $numRecords && $numRecords <= sizeof($testFiles) && file_exists(__DIR__.'/../Resources/LidoXML/'.$testFiles[$i])) {
            $testData = file_get_contents(__DIR__.'/../Resources/LidoXML/'.$testFiles[$i]);
            $this->client->request('POST', '/api/v1/data?access_token='.$accessToken, [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $testData);
            $response = $this->client->getResponse();
            $this->assertTrue($response->isSuccessful());
            $this->assertEquals(201, $response->getStatusCode());
            $i++;
        }
    }

    public function testListIdentifiers() {
        $this->numTestRecords = $this->paginationSize * 2; // make sure resumptionToken is tested

        $this->loadTestDataIntoMongo($this->numTestRecords);

        $verb = 'ListIdentifiers';
        $this->client->request('GET', '/oai/?verb='.$verb.'&metadataPrefix=oai_lido');

        $response = $this->client->getResponse()->getContent();
        $xmlResponse = $response; // keep a copy of the original response in XML format
        $response = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $response = $response[$verb];

        $this->assertEquals($this->paginationSize, sizeof($response['header']));
        $this->assertTrue(strpos($xmlResponse, '<resumptionToken completeListSize="'.$this->numTestRecords.'">') !== 0);

        $resumptionToken = $response['resumptionToken'];

        $this->client->request('GET', '/oai/?verb='.$verb.'&resumptionToken='.$resumptionToken);
        $response = $this->client->getResponse()->getContent();
        $xmlResponse = $response;
        $response = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $response = $response[$verb];
        $this->assertEquals($this->paginationSize, sizeof($response['header']));
        $this->assertTrue(strpos($xmlResponse, '<resumptionToken completeListSize="'.$this->numTestRecords.'"/>') !== 0); // no more resumptionToken value because end of list
    }

    public function testListRecords() {
        $this->numTestRecords = $this->paginationSize * 2; // make sure resumptionToken is tested

        $verb = 'ListRecords';
        $this->client->request('GET', '/oai/?verb='.$verb.'&metadataPrefix=oai_lido');
        $response = $this->client->getResponse()->getContent();
        $xmlResponse = $response; // keep a copy of the original response in XML format
        $response = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $response = $response[$verb];
        $this->assertEquals($this->paginationSize, sizeof($response['record']));
        $this->assertTrue(strpos($xmlResponse, '<resumptionToken completeListSize="'.$this->numTestRecords.'">') !== 0);

        $resumptionToken = $response['resumptionToken'];

        $this->client->request('GET', '/oai/?verb='.$verb.'&resumptionToken='.$resumptionToken);
        $response = $this->client->getResponse()->getContent();
        $xmlResponse = $response;
        $response = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $response = $response[$verb];
        $this->assertEquals($this->paginationSize, sizeof($response['record']));
        $this->assertTrue(strpos($xmlResponse, '<resumptionToken completeListSize="'.$this->numTestRecords.'"/>') !== 0); // no more resumptionToken value because end of list
    }

    public function testGetRecord() {
        // Fetch record with existing ID
        $verb = 'GetRecord';
        $id = 'http://mskgent.be/collection/work/data/S-40';
        $this->client->request('GET', '/oai/?verb='.$verb.'&identifier='.$id.'&metadataPrefix=oai_lido');
        $response = $this->client->getResponse()->getContent();
        $xmlResponse = $response;
        $response = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $response = $response[$verb];
        $this->assertTrue(strpos($xmlResponse, '<lido:appellationValue xml:lang="nl">Het Manna</lido:appellationValue>') !== 0);

        // Fetch non-existent record
        $id = 'doesntexist';
        $this->client->request('GET', '/oai/?verb='.$verb.'&identifier='.$id.'&metadataPrefix=oai_lido');
        $response = $this->client->getResponse()->getContent();
        $xmlResponse = $response;
        $this->assertTrue(strpos($xmlResponse, '<error code="idDoesNotExist">') !== 0);
    }

    protected function tearDown() {

    }

}
