<?php

namespace VKC\DataHub\ResourceBundle\Data\Converter;

use VKC\DataHub\ResourceBundle\Service\CatmanduService;

/**
 * An implementation of a VKC DataHub Data Converter
 * which uses Catmandu for data converting data
 * to and from LidoXML.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class CatmanduLidoXMLDataConverter extends AbstractCatmanduDataConverter
{
    const CONVERTOR_ID = 'LIDOXML';
    /**
     * {@inheritDoc}
     */
    protected $sourceFormat = 'LIDO';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->id = static::CONVERTOR_ID;
    }
}
