<?php

namespace DataHub\ResourceBundle\Service\Builder;

use Datahub\ResourceBundle\Service\DataType\DataTypeRegisterInterface;
use Datahub\ResourceBundle\Service\Converter\SabreXMLConverterService;

class ConverterFactory implements ConverterFactoryInterface {

    protected $dataTypeRegister;

    protected $converter;

    public function __construct(DataTypeRegisterInterface $dataTypeRegister) {
        $this->dataTypeRegister = $dataTypeRegister;
    }

    public function setConverter($dataType) {
        if ($class = $this->dataTypeRegister->getDataType($dataType)) {
            $dataType = new $class();
            $this->converter = new SabreXMLConverterService($dataType);
        } else {
            throw new Exception(sprintf('Could not instantiate a converter because format %s is not registered.'), $dataType);
        }

        return true;
    }

    public function getConverter($dataType) {
        return $this->converter;
    }

}
