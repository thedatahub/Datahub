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
    const CONVERTOR_ID = 'lidoxml';
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

    /**
     * {@inheritDoc}
     */
    public function getRecords(array $data)
    {
        // LIDO already splits the different records
        return $data;
    }


    /**
     * {@inheritDoc}
     */
    public function getRecordDataPids(array $dataRecord)
    {
        // return each lidoRecID
        return array_column($dataRecord['lidoRecID'], '_');
    }

    /**
     * {@inheritDoc}
     */
    public function getRecordObjectPids(array $dataRecord)
    {
        // return each objectPublishedID
        return isset($dataRecord['objectPublishedID'])? array_column($dataRecord['objectPublishedID'], '_') : null;
    }
}
