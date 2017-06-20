<?php

namespace DataHub\ResourceBundle\Service\Builder;

/**
 * Factory interface
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
interface ConverterFactoryInterface {
    public function setConverter($dataType);
    public function getConverter();
}
