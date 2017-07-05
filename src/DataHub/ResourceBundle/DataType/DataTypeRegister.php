<?php

namespace DataHub\ResourceBundle\DataType;

/**
 * Datatype Register class.
 *
 * This register contains all the available datatypes within the datahub.
 * These datatypes are currently implemented:
 *
 *  - LIDO XML http://www.lido-schema.org
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
class DataTypeRegister implements DataTypeRegisterInterface {

    protected $dataTypes;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->dataTypes = [
            'lido' => 'DataHub\ResourceBundle\DataType\DataTypeLido',
            // 'marcxml' => 'DataHub\ResourceBundle\Service\DataType\DataTypeMARCXml',
            // 'dc' => 'DataHub\ResourceBundle\Service\DataType\DataTypeDublinCore',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDataType($id) {
        $result = false;

        if (isset($this->dataTypes[$id])) {
            $result = $this->dataTypes[$id];
        }

        return $result;
    }
}