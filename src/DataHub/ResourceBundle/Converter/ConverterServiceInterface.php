<?php

namespace DataHub\ResourceBundle\Converter;

use DataHub\ResourceBundle\DataType\DataTypeInterface;

/**
 * Converter Service interface
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
interface ConverterServiceInterface {
    /**
     * Constructor.
     *
     * @param DateTypeInterfacer The dataType definition
     */
    public function __construct(DataTypeInterface $dataType);

    /**
     * Converts and validates a string of serialized data into an object.
     *
     * @param string $serializedData The data to be set and validated.
     */
    public function read($serializedData);

    /**
     * Converts an object into a serialized string.
     *
     * @param mixed $object The object to be serialized.
     */
    public function write($object);

    /**
     * Get the DataType definition from the converter instance.
     */
    public function getDataType();
}
