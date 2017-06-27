<?php

namespace DataHub\ResourceBundle\Converter;

use DataHub\ResourceBundle\DataType\DataTypeInterface;
use Sabre\Xml\Service;
use DOMDocument;

/**
 * Converter service class.
 *
 * This converter wraps the Sabre XML library to (de)serialize objects.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
class SabreXMLConverterService implements ConverterServiceinterface {

    protected $service;

    public function __construct(DataTypeInterface $dataType) {
        $this->dataType = $dataType;
        $this->service = new Service();
        $this->service->namespaceMap = $dataType->getNamespaceMap();
    }

    public function validate($serializedData) {
        $catalog = $this->dataType->getCatalog();
        $schema = $this->dataType->getSchema();

        $schLoc = __DIR__.'/../Resources/schemas';
        foreach ($catalog as &$item) {
            $item = sprintf('%s%s', $schLoc, $item);
        }
        $schema = sprintf('%s%s', $schLoc, $schema);

        libxml_disable_entity_loader(false);
        libxml_set_external_entity_loader(
            function ($public, $system, $context) use ($catalog) {
                if (is_file($system)) {
                    return $system;
                }
                if (isset($catalog[$system])) {
                    return $catalog[$system];
                }

                return $system;
            }
        );

        $data_dom = new DOMDocument();
        $data_dom->loadXML($serializedData);
        return $data_dom->schemaValidate($schema);
    }

    public function read($serializedData) {
        $this->validate($serializedData);
        return $this->service->parse($serializedData);
    }

    public function write($object) {
        $rootElement = $this->dataType->getRootElement();
        return $this->service->write($rootElement, $object);
    }

    public function getDataType() {
        return $this->dataType;
    }
}
