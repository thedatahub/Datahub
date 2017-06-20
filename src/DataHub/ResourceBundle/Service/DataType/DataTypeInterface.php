<?php

namespace DataHub\ResourceBundle\Service\DataType;

/**
 * DataType interface
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
interface DataTypeInterface {
    public function getNamespaceMap();
    public function getRootElement();
}
