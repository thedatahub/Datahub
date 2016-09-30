<?php

namespace VKC\DataHub\SharedBundle\Service;

use Doctrine\MongoDB\Connection;
use VKC\DataHub\SharedBundle\Helper\SerializationHelper;

/**
 * A simple service containing some helpers for dealing
 * with MongoDB.
 */
class DocumentsService
{
    const OPTION_PREPARE_SERIALIZATION = 1;
    const OPTION_KEEP_ITERATOR = 2;
    const OPTION_COUNT_RESULTS = 4;
    const OPTION_DEFAULTS = 5;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $databaseName;

    /**
     * Set connection
     *
     * @param Connection $connection
     * @return DocumentsService
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set databaseName
     *
     * @param string $databaseName
     * @return DocumentsService
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }

    /**
     * Get the database.
     *
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->getConnection()->{$this->databaseName};
    }

    /**
     * Get a collection by name.
     *
     * @param  string $collectionName
     * @return mixed
     */
    public function getCollection($collectionName)
    {
        return $this->getConnection()->selectCollection($this->databaseName, $collectionName);
    }

    /**
     * Find results, fetching fields (if specified) based on a query.
     * Optionally limits based on offset and limit.
     * Optionally prepares data for serialization by converting MongoId and MongoDate objects to strings.
     *
     * @param  string       $collectionName
     * @param  array        $query
     * @param  array        $fields
     * @param  array|string $sort
     * @param  int|null     $offset
     * @param  int|null     $limit
     * @param  int|null     $options
     * @return mixed
     */
    public function find(
        $collectionName,
        $query = array(),
        $fields = array(),
        $sort = array(),
        $offset = null,
        $limit = null,
        $options = null
    ) {
        $collection = $this->getCollection($collectionName);

        if (!isset($options)) {
            $options = static::OPTION_DEFAULTS;
        }

        if (!isset($fields) || !$fields) {
            $fields = array();
        }

        if (is_string($sort)) {
            $sortParts = explode(',', $sort);

            if (count($sortParts) == 2) {
                $sort = array(
                    $sortParts[0] => strtolower($sortParts[1]) === 'asc' ? 1 : -1,
                );
            }
        }

        // Make fields array associative
        if (!$this->isAssociativeArray($fields)) {
            $fields = array_fill_keys($fields, true);
        }

        $data = $collection
            ->find(
                $query,
                $fields
            );

        // $count = -1;
        $count = $data->count();

        if (isset($sort) && !empty($sort)) {
            $data = $data
                ->sort($sort);
        }

        if ($offset !== null) {
            $data = $data->skip($offset);
        }

        if ($limit !== null) {
            $data = $data->limit($limit);
        }

        if (!($options & static::OPTION_KEEP_ITERATOR)) {
            $data = iterator_to_array($data);
            $data = array_values($data);

            if ($options & static::OPTION_PREPARE_SERIALIZATION) {
                SerializationHelper::normalizeData($data);
            }

            if ($options & static::OPTION_COUNT_RESULTS) {
                $data = [
                    'count'   => $count,
                    'results' => $data,
                ];
            }
        }

        return $data;
    }

    /**
     * Find a result, fetching fields (if specified) based on a query.
     * Limit to one result.
     * Optionally prepares data for serialization by converting MongoId and MongoDate objects to strings.
     *
     * @param  string       $collectionName
     * @param  array        $query
     * @param  array        $fields
     * @param  int|null     $options
     * @return mixed
     */
    public function findOne(
        $collectionName,
        $query = array(),
        $fields = array(),
        $options = null
    ) {
        if (!isset($options)) {
            $options = static::OPTION_DEFAULTS;
        }

        $options = $options & ~static::OPTION_COUNT_RESULTS;
        $options = $options & ~static::OPTION_KEEP_ITERATOR;

        $result = $this->find($collectionName, $query, $fields, null, null, 1, $options);
        $result = count($result) ? $result[0] : null;

        return $result;
    }

    /**
     * Get connection.
     *
     * @return mixed
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Return a boolean indicating whether or not the given array is
     * associative.
     *
     * @param  array  $array
     * @return boolean
     */
    protected function isAssociativeArray($array)
    {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }
}
