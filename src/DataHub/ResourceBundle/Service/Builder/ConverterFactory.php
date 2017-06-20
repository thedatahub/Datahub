<?php

namespace DataHub\ResourceBundle\Service\Builder;

use DataHub\ResourceBundle\Service\DataType\DataTypeRegisterInterface;
use DataHub\ResourceBundle\Service\Converter\SabreXMLConverterService;

/**
 * Factory class.
 *
 * Create a new instance of SabreXMLConverterService, set the DataType to the
 * relevant XML schema.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
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

    public function getConverter() {
        return $this->converter;
    }

}
