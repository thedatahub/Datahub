<?php

namespace DataHub\ResourceBundle\Service\Converter;

interface ConverterServiceinterface {
    public function __construct(DataTypeInterface $dataType)
    public function validate($serialzedData);
    public function read($serializedData);
    public function write($object);
}
