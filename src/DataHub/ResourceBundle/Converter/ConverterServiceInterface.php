<?php

namespace DataHub\ResourceBundle\Converter;

use DataHub\ResourceBundle\DataType\DataTypeInterface;

/**
 * Converter Service interface
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
interface ConverterServiceinterface {
    public function __construct(DataTypeInterface $dataType);
    public function read($serializedData);
    public function write($object);
    public function getDataType();
}
