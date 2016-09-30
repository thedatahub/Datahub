<?php

namespace VKC\DataHub\ResourceBundle\Data\Converter;

/**
 * A base implementation of a VKC DataHub Data Converter,
 * allowing for easy extension.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
abstract class AbstractDataConverter implements DataConverterInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Fetch class name and remove namespace portion
        $type = explode('\\', get_class($this));
        $type = end($type);
        $type = preg_replace('/DataConverter$/', '', $type);

        if (!$this->id) {
            $this->id = $type;
        }
    }

    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Convert a PHP array to raw data.
     *
     * @param  array $data
     * @return mixed
     */
    abstract public function fromArray($data);

    /**
     * Convert raw data to a PHP array.
     *
     * @param  mixed $rawData
     * @return array|null
     */
    abstract public function toArray($rawData);
}
