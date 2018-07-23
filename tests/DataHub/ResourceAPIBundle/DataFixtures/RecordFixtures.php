<?php

namespace DataHub\ResourceAPIBundle\DataFixtures;

use DataHub\SharedBundle\DataFixtures\EnvironmentSpecificDataFixture as AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use DataHub\ResourceAPIBundle\Document\Record;

class RecordFixtures extends AbstractFixture implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    protected function doLoad(ObjectManager $manager)
    {
        $raw = file_get_contents(__DIR__.'/../../Resources/lido.xml');
        $jsonStr = file_get_contents(__DIR__.'/../../Resources/lido.json');

        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $doc->loadXML($raw);

        foreach (range(1, 10) as $number) {
            $identifier = sprintf('identifier-%s', $number);
            $doc->getElementsByTagName("lidoRecID")->item(0)->nodeValue = $identifier;
            $raw = $doc->saveXML();

            $json = json_decode($jsonStr, JSON_OBJECT_AS_ARRAY);
            foreach ($json['json'] as $key => $value) {
                if ($value['name'] == '{http://www.lido-schema.org}lidoRecID') {
                    $json['json'][$key]['value'] = $identifier;
                    break;
                }
            }
            $jsonStr = json_encode($json);

            $record = new Record();
            $record->setRecordIds(array($identifier));
            $record->setObjectIds(array());
            $record->setRaw($raw);
            $record->setJson($jsonStr);
    
            $manager->persist($record);
            $manager->flush();
        }
    }
}
