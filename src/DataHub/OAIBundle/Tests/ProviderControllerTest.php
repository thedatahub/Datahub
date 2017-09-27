<?php

namespace DataHub\OAIBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

use DataHub\OAIBundle\Repository\Repository;

/**
 * Functional testing for ProviderController
 *
 * Functional testing suite for the Datahub OAI Endpoint.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\OAIBundle
 */
class ProviderControllerTest extends WebTestCase {

    /**
     * {@inheridoc}
     */
    public static function setUpBeforeClass()
    {
        $validRecord = file_get_contents(__DIR__.'/../Resources/LidoXML/LIDO-Example_FMobj00154983-LaPrimavera.xml');

        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $doc->loadXML($validRecord);

        foreach (range(1, 10) as $number) {
            $doc->getElementsByTagName("lidoRecID")->item(0)->nodeValue = sprintf('identifier-%s', $number);
            $record = $doc->saveXML();
            $response = ProviderControllerTest::post($record);
        }

        ProviderControllerTest::post($validRecord);
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        foreach (range(1, 10) as $number) {
            $id = sprintf('identifier-%s', $number);
            ProviderControllerTest::delete($id);
        }

        $d = urlencode('DE-Mb112/lido-obj00154983');
        ProviderControllerTest::delete($d);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->client = static::createClient();
    }

    /**
     * Gets an OAuth access token
     *
     * @return string A valid OAuth access token.
     */
    protected static function getAccessToken() {
        $client = static::createClient();
        $client->request('GET', '/oauth/v2/token?grant_type=password&username=admin&password=datahub&client_id=slightlylesssecretpublicid&client_secret=supersecretsecretphrase');
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        return $data['access_token'];
    }

    /**
     * Implements a POST client call.
     *
     * @param string $record Required valid record string.
     *
     * @param Symfony\Component\HttpFoundation\Response Response object
     */
    protected static function post($record) {
        $accessToken = ProviderControllerTest::getAccessToken();
        $action = sprintf('/api/v1/data?access_token=%s', $accessToken);

        $client = static::createClient();
        $client->request('POST', $action, [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $record);

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
    protected static function delete($id) {
        $accessToken = ProviderControllerTest::getAccessToken();
        $action = sprintf("/api/v1/data/%s?access_token=%s", $id, $accessToken);

        $client = static::createClient();
        $client->request('DELETE', $action);

        return $client->getResponse();
    }

    /**
     * Implements OAI Identify verb
     */
    public function identify()
    {
        $action = sprintf('/oai/?verb=%s', 'Identify');
        $this->client->request('GET', $action);
        return $this->client->getResponse();
    }

    /**
     * Implements OAI ListMetdataFormats verb
     */
    public function listMetdataFormats()
    {
        $action = sprintf('/oai/?verb=%s', 'ListMetadataFormats');
        $this->client->request('GET', $action);
        return $this->client->getResponse();
    }

    /**
     * Implements OAI ListSets verb
     */
    public function listSets()
    {
        $action = sprintf('/oai/?verb=%s', 'ListSets');
        $this->client->request('GET', $action);
        return $this->client->getResponse();
    }

    /**
     * Implements OAI ListIdentifiers verb
     */
    public function listIdentifiers($resumptionToken = null, $from = null, $until = null)
    {
        $action = sprintf('/oai/?verb=%s&metadataPrefix=%s', 'ListIdentifiers', 'oai_lido');

        if (!is_null($resumptionToken)) {
            $action = sprintf('/oai/?verb=%s&resumptionToken=%s', 'ListIdentifiers', $resumptionToken);
        }

        if (!is_null($from)) {
            $action = sprintf('%s&from=%s', $action, $from);
        }

        if (!is_null($until)) {
            $action = sprintf('%s&until=%s', $action, $until);
        }

        $this->client->request('GET', $action);
        return $this->client->getResponse();
    }

    /**
     * Implements OAI ListRecords verb
     */
    public function listRecords($resumptionToken = null)
    {
        $action = sprintf('/oai/?verb=%s&metadataPrefix=%s', 'ListRecords', 'oai_lido');

        if (!is_null($resumptionToken)) {
            $action = sprintf('/oai/?verb=%s&resumptionToken=%s', 'ListRecords', $resumptionToken);
        }

        $this->client->request('GET', $action);
        return $this->client->getResponse();
    }

    /**
     * Implements OAI GetRecord verb
     *
     * @param $id The identifier used to get a particular record.
     */
    public function getRecord($id)
    {
        $action = sprintf('/oai/?verb=%s&metadataPrefix=%s&identifier=%s', 'GetRecord', 'oai_lido', $id);
        $this->client->request('GET', $action);
        return $this->client->getResponse();
    }

    /**
     * Converts an XML string into an \SimpleXMLElement object.
     *
     * @param $content The raw XML string
     * @return SimpleXMLElement A SimpleXMLElement encapsulating the XML string
     */
    public function xml($content)
    {
        $xml = new \SimpleXMLElement($content);

        // We need to explicitely set the xmlns namespaces. xpath() does not
        // work if we don't do this.
        $xml->registerXPathNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
        $xml->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->registerXPathNamespace('lido', 'http://www.lido-schema.org');
        $xml->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
        $xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');

        return $xml;
    }

    public function testVerbIdentify()
    {
        $response = $this->identify();

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(1, $xml->xpath('//oai:Identify[oai:repositoryName="Datahub OAI"]'));
        $this->assertCount(1, $xml->xpath('//oai:Identify[oai:baseURL="http://localhost"]'));
        $this->assertCount(1, $xml->xpath('//oai:Identify[oai:adminEmail="hello@organisation.com"]'));
    }

    public function testVerbListMetadataFormats()
    {
        $response = $this->listMetdataFormats();

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(1, $xml->xpath('//oai:metadataFormat[oai:metadataPrefix="oai_dc"]'));
        $this->assertCount(1, $xml->xpath('//oai:metadataFormat[oai:metadataPrefix="oai_rdf"]'));
        $this->assertCount(1, $xml->xpath('//oai:metadataFormat[oai:metadataPrefix="oai_lido"]'));
    }

    public function testVerbListSets()
    {
        // @todo
        //   To be implemented if we decide to support sets
    }

    public function testListIdentifiers()
    {
        $response = $this->listIdentifiers();

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(4, $xml->xpath('//oai:header/oai:identifier'));
        $this->assertCount(1, $xml->xpath('//oai:resumptionToken'));

        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-1"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-2"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-3"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-4"]'));
        $this->assertCount(0, $xml->xpath('//oai:header[oai:identifier="identifier-5"]'));

        // Test support for resumptionToken

        $nodes = $xml->xpath('//oai:resumptionToken');
        $resumptionToken = (string) array_pop($nodes);

        $response = $this->listIdentifiers($resumptionToken);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(0, $xml->xpath('//oai:header[oai:identifier="identifier-4"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-5"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-6"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-7"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-8"]'));
        $this->assertCount(0, $xml->xpath('//oai:header[oai:identifier="identifier-9"]'));

        $nodes = $xml->xpath('//oai:resumptionToken');
        $resumptionToken = (string) array_pop($nodes);

        $response = $this->listIdentifiers($resumptionToken);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(0, $xml->xpath('//oai:header[oai:identifier="identifier-8"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-9"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-10"]'));

        // Test if an invalid resumptionToken was given

        $response = $this->listIdentifiers('invalidresumptiontoken');

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(400, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $nodes = $xml->xpath('//oai:error');
        $errorMessage = (string) array_pop($nodes);

        $this->assertEquals('An invalid resumptionToken was given', $errorMessage);

        // Test From / Until

        $from = new \DateTime();
        $from->modify('-1 day');
        $from = $from->format('Y-m-d\TH:i:s\Z');

        $until = new \DateTime();
        $until->modify("+1 day");
        $until = $until->format('Y-m-d\TH:i:s\Z');

        $response = $this->listIdentifiers(null, $from, $until);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(4, $xml->xpath('//oai:header/oai:identifier'));
        $this->assertCount(1, $xml->xpath('//oai:resumptionToken'));

        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-1"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-2"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-3"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-4"]'));
        $this->assertCount(0, $xml->xpath('//oai:header[oai:identifier="identifier-5"]'));

        // Test From / Until with ResumptionToken

        $nodes = $xml->xpath('//oai:resumptionToken');
        $resumptionToken = (string) array_pop($nodes);

        $response = $this->listIdentifiers($resumptionToken);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(0, $xml->xpath('//oai:header[oai:identifier="identifier-4"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-5"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-6"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-7"]'));
        $this->assertCount(1, $xml->xpath('//oai:header[oai:identifier="identifier-8"]'));
        $this->assertCount(0, $xml->xpath('//oai:header[oai:identifier="identifier-9"]'));

        // Test From / Until without a result

        $from = new \DateTime();
        $from->modify('-3 days');
        $from = $from->format('Y-m-d\TH:i:s\Z');

        $until = new \DateTime();
        $until->modify("-2 days");
        $until = $until->format('Y-m-d\TH:i:s\Z');

        $response = $this->listIdentifiers(null, $from, $until);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $nodes = $xml->xpath('//oai:error');
        $errorMessage = (string) array_pop($nodes);

        $this->assertEquals('The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.', $errorMessage);
    }

    public function testListRecords()
    {
        $response = $this->listRecords();

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();


        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(4, $xml->xpath('//oai:record'));
        $this->assertCount(1, $xml->xpath('//oai:record/oai:metadata/lido:lido[lido:lidoRecID="identifier-1"]'));

    }

    public function testVerbGetRecord()
    {
        $response = $this->getRecord('DE-Mb112/lido-obj00154983');

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(200, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $this->assertCount(1, $xml->xpath('//oai:record'));
        $this->assertCount(1, $xml->xpath('//oai:record/oai:metadata/lido:lido[lido:lidoRecID="DE-Mb112/lido-obj00154983"]'));
    }

    public function testVerbGetRecordNotFound()
    {
        $response = $this->getRecord('does-not-exist');

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $this->assertEquals(400, $statusCode);
        $this->assertNotEmpty($content);

        $xml = $this->xml($content);

        $error = $xml->xpath('//oai:error');
        $this->assertEquals("No matching identifier does-not-exist", $error[0][0]);
    }
}