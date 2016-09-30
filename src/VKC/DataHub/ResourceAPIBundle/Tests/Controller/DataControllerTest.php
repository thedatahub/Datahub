<?php

namespace VKC\DataHub\ResourceAPIBundle\Tests\Controller;

use VKC\DataHub\OAuthBundle\Tests\OAuthTestCase;

class DataControllerTest extends OAuthTestCase
{
    public function testDataCrudAction()
    {
        $testDataList = [
            'CatmanduLidoXML' => [
                file_get_contents(__DIR__.'/../Resources/LidoXML/LIDO-Example_FMobj00154983-LaPrimavera.xml'),
                file_get_contents(__DIR__.'/../Resources/LidoXML/LIDO-Example_FMobj20344012-Fontana_del_Moro.xml'),
            ],
        ];

        list($crawler, $response, $data) = $this->apiRequest('GET', '/resources/data/converters');
        $this->assertTrue($response->isSuccessful());
        $this->assertCRUDListContent($data);
        $dataConverterIds = $data['results'];

        foreach ($dataConverterIds as $dataConverterId) {
            if (!isset($testDataList[$dataConverterId])) {
                continue;
            }

            foreach ($testDataList[$dataConverterId] as $testData) {
                list($crawler, $response, $data) = $this->apiRequest('POST', '/resources/data', [
                    'format' => $dataConverterId,
                    'data'   => $testData,
                ]);
                $this->assertTrue($response->isSuccessful());
                $this->assertArrayHasKey('_id', $data);
                $dataId = $data['_id'];

                list($crawler, $response, $data) = $this->apiRequest('GET', '/resources/data');
                $this->assertTrue($response->isSuccessful());
                $this->assertCRUDListContent($data);
                $this->assertGreaterThan(0, $data['count']);

                list($crawler, $response, $data) = $this->apiRequest('GET', "/resources/data/{$dataId}");
                $this->assertTrue($response->isSuccessful());
                $this->assertArrayHasKey('_id', $data);
                $this->assertEquals($data['_id'], $dataId);

                list($crawler, $response, $data) = $this->apiRequest('DELETE', "/resources/data/{$dataId}");
                $this->assertTrue($response->isSuccessful());

                list($crawler, $response, $data) = $this->apiRequest('GET', "/resources/data/{$dataId}");
                $this->assertFalse($response->isSuccessful());
            }
        }
    }
}
