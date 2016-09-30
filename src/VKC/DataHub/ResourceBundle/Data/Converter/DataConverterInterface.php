<?php

namespace VKC\DataHub\ResourceBundle\Data\Converter;

/**
 * An interface defining the behaviour of a VKC DataHub Data Converter.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
interface DataConverterInterface
{
    public function getId();
    public function fromArray($data);
    public function toArray($rawData);
}
