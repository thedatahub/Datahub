<?php

namespace DataHub\SharedBundle\Service;

use Doctrine\MongoDB\Connection;
use DataHub\SharedBundle\Helper\SerializationHelper;

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
     * @var Monolog\Logger
     */
    protected $logger;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $databaseName;

    /**
     * Constructor
     *
     * @param Monolog\Logger $logger
     * @param Connection     $connection
     */
    public function __construct($logger, $connection = null, $databaseName = null)
    {
        $this->setLogger($logger);
        if (isset($connection)) $this->setConnection($connection);
        if (isset($databaseName)) $this->setDatabaseName($databaseName);

        $this->logger->debug('Initialized DocumentsService');
    }

    /**
     * Set logger service.
     *
     * @param  Monolog\Logger $logger
     * @return DocumentsService
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

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
        $this->logger->debug(strtr('Get collection {collection} from database {database}', [
            '{collection}' => $collectionName,
            '{database}'   => $this->databaseName,
        ]));

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

        // Convert certain values into objects
        $this->convertValues($query);

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

        // Convert certain values into objects
        $this->convertValues($query);

        $result = $this->find($collectionName, $query, $fields, null, null, 1, $options);
        $result = count($result) ? $result[0] : null;

        return $result;
    }

    /**
     * Insert data into collection
     *
     * @param  string $collectionName Name of the collection
     * @param  array  $data           Data array
     *
     * @return mixed                  Id of the inserted data if available, otherwise true upon success
     */
    public function insert($collectionName, $data)
    {
        $this->logger->debug('Insert data into ' . $collectionName);

        // Convert certain values into objects
        $this->convertValues($data);

        // Convert ISO8601 dates into MongoDate
        // TODO: recursively loop over all array values, converting ISO8601 to MongoDate

        $result = $this->getCollection($collectionName)->insert($data);

        return (is_array($result) && isset($result['upserted']))? $result['upserted'] : true;
    }

    /**
     * Update data in collection
     *
     * @param  string $collectionName Name of the collection
     * @param  array  $query          Query array
     * @param  array  $changeset      Array with changes
     *
     * @return boolean                True upon success
     */
    public function update($collectionName, $query, $changeset)
    {
        // Convert certain values into objects
        $this->convertValues($query);
        $this->convertValues($changeset);

        $result = $this->getCollection($collectionName)->update($query, $changeset);

        return (is_array($result) && isset($result['upserted']))? $result['upserted'] : true;
    }

    /**
     * Remove data from collection
     *
     * @param  string $collectionName Name of the collection
     * @param  array  $query          Query array
     *
     * @return boolean                True upon success
     */
    public function remove($collectionName, $query)
    {
        // Convert certain values into objects
        $this->convertValues($query);

        $result = $this->getCollection($collectionName)->remove($query);

        return $result['n'] > 0;
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

    /**
     * Convert certain values into objects
     *
     * @param mixed $data
     */
    protected function convertValues(&$data)
    {
        if (is_array($data)) {
            foreach (array_keys($data) as $key) {
                if (isset($data[$key])) {
                    $this->convertValues($data[$key]);
                }
            }
        }
        else {
            // TODO: Convert ISO8601 dates into MongoDate
        }
    }
}
