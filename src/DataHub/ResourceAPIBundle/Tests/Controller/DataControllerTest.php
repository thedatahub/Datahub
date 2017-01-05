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
                list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', array(), array(), array(), array(), ['CONTENT_TYPE' => 'application/lido+xml'], $testData);
                $this->assertTrue($response->isSuccessful());

                // Get document PID from the response's location header
                $dataId = urldecode(array_values(array_reverse(explode('/', $response->headers->get('location'))))[0]);

                // Get all documents, must be > 0
                list($crawler, $response, $data) = $this->apiRequest('GET', '/v1/data');
                $this->assertTrue($response->isSuccessful());
                $this->assertCRUDListContent($data);
                $this->assertGreaterThan(0, $data['count']);

                // Get inserted document by PID
                list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/{$dataId}");
                $this->assertTrue($response->isSuccessful());
                $this->assertEquals($data['data_pids'][0], $dataId);
                $this->assertEquals($data['raw'], $testData);

                // Delete a document by PID
                list($crawler, $response, $data) = $this->apiRequest('DELETE', "/v1/data/{$dataId}", array(), array(), array(), array(), ['CONTENT_TYPE' => 'application/lido+xml']);
                $this->assertTrue($response->isSuccessful());
                $this->assertEquals($response->getStatusCode(), 204);

                // Check if document actually removed from storage
                list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/{$dataId}");
                $this->assertFalse($response->isSuccessful());
            }
        }
    }
}
