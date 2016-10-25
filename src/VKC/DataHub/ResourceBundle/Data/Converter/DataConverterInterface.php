<?php

namespace VKC\DataHub\ResourceBundle\Data\Converter;

/**
 * An interface defining the behaviour of a VKC DataHub Data Converter.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 * @author Tom Van den Abbeele <tom.vda@inuits.eu>
 */
interface DataConverterInterface
{
    public function getId();
    public function toArray($rawData);
    public function fromArray(array $data);
    public function getRecords(array $data);
    public function getRecordDataPids(array $dataRecord);
    public function getRecordObjectPids(array $dataRecord);
}
