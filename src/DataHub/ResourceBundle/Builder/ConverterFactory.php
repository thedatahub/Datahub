<?php

namespace DataHub\ResourceBundle\Builder;

use DataHub\ResourceBundle\DataType\DataTypeRegisterInterface;
use DataHub\ResourceBundle\Converter\SabreXMLConverterService;

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

    /**
     * Constructor
     *
     * @param DataTypeRegisterInterface The dataType register which contains all
     *   available dataType definitions.
     */
    public function __construct(DataTypeRegisterInterface $dataTypeRegister) {
        $this->dataTypeRegister = $dataTypeRegister;
    }

    /**
     * {@inheritdoc}
     */
    public function setConverter($dataType) {
        if ($class = $this->dataTypeRegister->getDataType($dataType)) {
            $dataType = new $class();
            $this->converter = new SabreXMLConverterService($dataType);
        } else {
            throw new Exception(sprintf('Could not instantiate a converter because format %s is not registered.'), $dataType);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getConverter() {
        return $this->converter;
    }
}
