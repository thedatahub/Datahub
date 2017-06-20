<?php

namespace DataHub\ResourceBundle\Service\Converter;

use DataHub\ResourceBundle\Service\DataType\DataTypeInterface;
use Sabre\Xml\Service;

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
    }

    public function read($serializedData) {
        $object = $this->service->parse($serializedData);
        return $object;
    }

    public function write($object) {
        $rootElement = $this->dataType->getRootElement();
        $serialisedData = $this->service->write($rootElement, $object);
        return $serialisedData;
    }
}
