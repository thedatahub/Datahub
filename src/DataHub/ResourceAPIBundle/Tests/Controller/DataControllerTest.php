<?php

namespace DataHub\ResourceAPIBundle\Tests\Controller;

use DataHub\OAuthBundle\Tests\OAuthTestCase;

class DataControllerTest extends OAuthTestCase
{

    public function testDataCrudAction()
    {
        $testDataList = [
            'lidoxml' => [
                file_get_contents(__DIR__.'/../Resources/LidoXML/LIDO-Example_FMobj00154983-LaPrimavera.xml'),
                file_get_contents(__DIR__.'/../Resources/LidoXML/LIDO-Example_FMobj20344012-Fontana_del_Moro.xml'),
            ],
        ];

        list($crawler, $response, $data) = $this->apiRequest('GET', '/v1/data/converters');
        $this->assertTrue($response->isSuccessful());
        $this->assertCRUDListContent($data);
        $dataConverterIds = $data['results'];

        foreach ($dataConverterIds as $dataConverterId) {
            if (!isset($testDataList[$dataConverterId])) {
                continue;
            }

            foreach ($testDataList[$dataConverterId] as $testData) {
                // Insert a document
                list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', [], [], [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $testData);
                $this->assertTrue($response->isSuccessful());
                $this->assertEquals(201, $response->getStatusCode());

                // Get document PID from the response's location header
                $dataId = urldecode(array_values(array_reverse(explode('/', $response->headers->get('location'))))[0]);

                // Get all documents, must be > 0
                list($crawler, $response, $data) = $this->apiRequest('GET', '/v1/data');
                $this->assertTrue($response->isSuccessful());
                $this->assertEquals(200, $response->getStatusCode());
                $this->assertCRUDListContent($data);
                $this->assertGreaterThan(0, $data['count']);

                // Get inserted document by PID
                list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/{$dataId}");
                $this->assertTrue($response->isSuccessful());
                $this->assertEquals(200, $response->getStatusCode());
                $this->assertEquals($dataId, $data['data_pids'][0]);
                $this->assertEquals($testData, $data['raw']);

                // Update a document with given PID
                $testData2 = str_replace('Man-Made Object', 'test update', $testData);
                list($crawler, $response, $data) = $this->apiRequest('PUT', "/v1/data/{$dataId}", [], [], [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $testData2);
                $this->assertTrue($response->isSuccessful());
                $this->assertEquals(204, $response->getStatusCode());

                // Retrieve updated document, check against var
                list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/{$dataId}");
                $this->assertTrue($response->isSuccessful());
                $this->assertEquals(200, $response->getStatusCode());
                $this->assertEquals($dataId, $data['data_pids'][0]);
                $this->assertNotEquals($testData, $data['raw']);
                $this->assertEquals($testData2, $data['raw']);

                // Delete a document by PID
                list($crawler, $response, $data) = $this->apiRequest('DELETE', "/v1/data/{$dataId}", [], [], [], [], ['CONTENT_TYPE' => 'application/lido+xml']);
                $this->assertTrue($response->isSuccessful());
                $this->assertEquals(204, $response->getStatusCode());

                // Check if document actually removed from storage
                list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/{$dataId}");
                $this->assertFalse($response->isSuccessful());
                $this->assertEquals(404, $response->getStatusCode());
            }
        }
    }

    /**
     * Tests passing non LIDO XML data with that content type header.
     * Tests passing an unsupported format.
     */
    public function testNonLidoXMLInput() {
        $testData = "this is not XML data!";
        list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', [], [], [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $testData);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(400, $response->getStatusCode());

        $testData = file_get_contents(__DIR__.'/../Resources/LidoXML/LIDO-Example_FMobj00154983-LaPrimavera-invalid-lido.xml'); // file misses required tags
        list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', [], [], [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $testData);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(400, $response->getStatusCode());

        $testData = "{\"this is\": \"not an accepted input format\"}";
        list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $testData);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(415, $response->getStatusCode()); // Unsupported media type
    }

    /**
     * Tests inserting the same file twice.
     */
    public function testDuplicateInput() {
        $testData = file_get_contents(__DIR__.'/../Resources/LidoXML/LIDO-Example_FMobj00154983-LaPrimavera.xml');
        list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', [], [], [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $testData);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(201, $response->getStatusCode());

        list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', [], [], [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $testData);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Tests inserting the same file twice.
     */
    public function testPermissions() {
        /*
        $testData = file_get_contents(__DIR__.'/../Resources/LidoXML/LIDO-Example_FMobj00154983-LaPrimavera.xml');

        $client = static::createClient();

        // create an invalid access token
        $query = urldecode(preg_replace('/\[\d+\]/', '[]', http_build_query(['access_token' => 'invalid access token'])));
        $uri = static::API_URI_PREFIX . '/v1/data' . '?' . $query;
        $serverParams = ['CONTENT_TYPE' => 'application/lido+xml'];

        list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/DE-Mb112%2flido-obj00154983");
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(404, $response->getStatusCode());

        $methods = ['POST', 'GET'];
        foreach ($methods as $method) {
            $crawler = $client->request($method, $uri, [], [], $serverParams, $testData);
            $response = $client->getResponse();
            $this->assertFalse($response->isSuccessful());
            $this->assertEquals(401, $response->getStatusCode()); // Unauthorized
        }

        list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/DE-Mb112%2flido-obj00154983");
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(404, $response->getStatusCode());

        // Insert a valid document
        list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', [], [], [], [], ['CONTENT_TYPE' => 'application/lido+xml'], $testData);
dump($response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(201, $response->getStatusCode());




        // Get document PID from the response's location header
        $dataId = urldecode(array_values(array_reverse(explode('/', $response->headers->get('location'))))[0]);

        $uri = static::API_URI_PREFIX . "/v1/data/{$dataId}" . '?' . $query;
        $methods = ['GET', 'PUT', 'DELETE'];
        foreach ($methods as $method) {
            $crawler = $client->request($method, $uri, [], [], $serverParams, $testData);
            $response = $client->getResponse();
            $this->assertFalse($response->isSuccessful());
            $this->assertEquals(401, $response->getStatusCode()); // Unauthorized

        }
        */
    }

}
