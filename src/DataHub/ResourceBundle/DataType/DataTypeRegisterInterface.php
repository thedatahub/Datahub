<?php

namespace DataHub\ResourceBundle\DataType;

/**
 * DataTypeRegister interface
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
interface DataTypeRegisterInterface {
    public function getDataType($id);
}
