<?php

namespace DataHub\ResourceBundle\Converter;

use DataHub\ResourceBundle\DataType\DataTypeInterface;
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
