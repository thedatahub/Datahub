<?php

namespace DataHub\ResourceBundle\DataType;

/**
 * DataTypeRegister interface
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
interface DataTypeRegisterInterface {
    /**
     * Get the dataType from a map of available/registered dataTypes.
     *
     * @param string $id The ID by which the dataType is identified.
     */
    public function getDataType($id);
}
