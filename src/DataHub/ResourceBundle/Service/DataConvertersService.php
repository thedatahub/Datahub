<?php

namespace DataHub\ResourceBundle\Service;

use DataHub\ResourceBundle\Data\Converter\DataConverterInterface;

/**
 * DataConvertersService is a service for converting work data resources
 * between generic arrays and several other formats.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class DataConvertersService
{
    /**
     * @var array
     */
    protected $converters = [];

    /**
     * Register a converter.
     *
     * @param  DataConverterInterface $dataConverter Converter to register.
     * @return DataConvertersService
     */
    public function registerConverter(DataConverterInterface $dataConverter)
    {
        $this->converters[$dataConverter->getId()] = $dataConverter;

        return $this;
    }

    /**
     * Get a list of available converters.
     *
     * @return array<string>
     */
    public function getConverterList()
    {
        $converters = $this->getConverters();

        return array_keys($converters);
    }

    /**
     * Get all converters.
     *
     * @return array<string,DataConverterInterface>
     */
    public function getConverters()
    {
        return $this->converters;
    }

    /**
     * Get a converter by ID.
     *
     * @param  string $id Converter ID.
     * @return DataConverterInterface
     */
    public function getConverter($id)
    {
        $converters = $this->getConverters();

        return $converters[$id];
    }
}
