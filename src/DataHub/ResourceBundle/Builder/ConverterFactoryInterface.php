<?php

namespace DataHub\ResourceBundle\Builder;

/**
 * Factory interface
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
interface ConverterFactoryInterface {
    /**
     * Set the converter.
     *
     * Build a converter based on the provided DataType argument.
     *
     * @param string $dataType The dataType.
     */
    public function setConverter($dataType);

    /**
     * Get the converter.
     *
     * @param string $dataType The dataType.
     * @return DataHub\ResourceBundle\Converter\ConverterServiceinterface
     */
    public function getConverter();
}
