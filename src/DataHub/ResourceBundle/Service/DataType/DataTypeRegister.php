<?php

namespace DataHub\ResourceBundle\Service\DataType;

class DataTypeRegister implements DataTypeRegisterInterface {

    protected $dataTypes;

    public function __construct() {
        $this->dataTypes = [
            'lido' => 'DataHub\ResourceBundle\Service\DataType\DataTypeLido',
            // 'marc' => 'DataHub\ResourceBundle\Service\DataType\DataTypeMARC',
            // 'dc' => 'DataHub\ResourceBundle\Service\DataType\DataTypeDublinCore',
        ];
    }

    public function getDataType($id) {
        $result = false;

        if (isset($this->dataTypes[$id])) {
            $result = $this->datatypes[$id];
        }

        return $result;
    }
}