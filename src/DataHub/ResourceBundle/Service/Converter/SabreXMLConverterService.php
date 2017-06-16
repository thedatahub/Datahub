<?php

namespace DataHub\ResourceBundle\Service\Converter;

class SabreXMLConverterService implements ConverterServiceinterface {

    protected $service;

    public function __construct(DataTypeInterface $dataType) {
        $this->dataType = $dataType;
        $this->service = new Service();
        $this->service->namespaceMap = $dataType->getNamespaceMap();
    }

    public function validate($serialzedData) {
    }

    public function read($serializedData) {
        $object = $this->service->parse($data);
        return $object;
    }

    public function write($object) {
        $rootElement = $this->dataType->getRootElement();
        $serialisedData = $this->service->write($rootElement, $object);
        return $serialisedData;
    }
}
