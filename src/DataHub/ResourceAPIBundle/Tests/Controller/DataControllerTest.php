<?php

namespace DataHub\ResourceAPIBundle\Tests\Controller;

use DataHub\OAuthBundle\Tests\OAuthTestCase;

class DataControllerTest extends OAuthTestCase
{
    public function testDataCrudAction()
    {
        $testDataList = [
            'LIDOXML' => [
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
                list($crawler, $response, $data) = $this->apiRequest('POST', '/v1/data', [
                    'format' => $dataConverterId,
                    'data'   => $testData,
                ]);
                $this->assertTrue($response->isSuccessful());
                $this->assertArrayHasKey('_id', $data);
                $dataId = $data['_id'];

                list($crawler, $response, $data) = $this->apiRequest('GET', '/v1/data');
                $this->assertTrue($response->isSuccessful());
                $this->assertCRUDListContent($data);
                $this->assertGreaterThan(0, $data['count']);

                list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/{$dataId}");
                $this->assertTrue($response->isSuccessful());
                $this->assertArrayHasKey('_id', $data);
                $this->assertEquals($data['_id'], $dataId);

                list($crawler, $response, $data) = $this->apiRequest('DELETE', "/v1/data/{$dataId}");
                $this->assertTrue($response->isSuccessful());

                list($crawler, $response, $data) = $this->apiRequest('GET', "/v1/data/{$dataId}");
                $this->assertFalse($response->isSuccessful());
            }
        }
    }
}