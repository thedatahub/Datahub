<?php

namespace VKC\DataHub\ResourceBundle\Data\Converter;

use VKC\DataHub\ResourceBundle\Service\CatmanduService;

/**
 * A base implementation of a VKC DataHub Data Converter
 * which uses Catmandu for data conversion.
 *
 * Internally, this converter will call the Catmandu
 * CLI to convert data from a source format to JSON,
 * capturing the output and decoding it into an array.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
abstract class AbstractCatmanduDataConverter extends AbstractDataConverter
{
    /**
     * @var string
     */
    protected $sourceFormat;

    /**
     * @var string
     */
    protected $targetFormat = 'JSON';

    /**
     * @var CatmanduService
     */
    protected $catmandu;

    /**
     * Set catmandu
     *
     * @param CatmanduService $catmandu
     * @return AbstractCatmanduDataConverter
     */
    public function setCatmandu(CatmanduService $catmandu)
    {
        $this->catmandu = $catmandu;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function fromArray($data)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }

        return $this->catmandu->convertData($this->targetFormat, $this->sourceFormat, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray($rawData)
    {
        $result = $this->catmandu->convertData($this->sourceFormat, $this->targetFormat, $rawData);

        if ($result) {
            $result = json_decode($result, true);
        }

        return $result;
    }
}
