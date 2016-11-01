<?php

namespace DataHub\ResourceBundle\Service;

use DataHub\SharedBundle\Service\DocumentsService;

/**
 * DataService is a service for managing work data resources.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class DataService
{
    /**
     * @var Monolog\Logger
     */
    protected $logger;

    /**
     * @var DocumentsService
     */
    protected $documentManager;

    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var string
     */
    protected $ownerId = null;

    /**
     * @var string
     */
    protected $anonymousUser = null;

    /**
     * Constructor
     *
     * @param Monolog\Logger   $logger
     * @param DocumentsService $documentManager
     * @param string           $collectionName
     */
    public function __construct($logger, $documentManager = null, $collectionName = null)
    {
        $this->setLogger($logger);

        if (isset($documentManager)) $this->setDocumentManager($documentManager);
        if (isset($collectioName))$this->setCollectionName($collectionName);

        $this->logger->debug('Initialized DataService');
    }

    /**
     * Set logger service.
     *
     * @param  Monolog\Logger $logger
     * @return LidoXmlDecoder
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set documentManager.
     *
     * @param DocumentsService $documentManager
     * @return DataService
     */
    public function setDocumentManager(DocumentsService $documentManager)
    {
        $this->documentManager = $documentManager;

        return $this;
    }

    /**
     * Set collectionName.
     *
     * @param string $collectionName
     * @return DataService
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;

        return $this;
    }

    /**
     * Set anonymousUser.
     *
     * @param string $user
     * @return DataService
     */
    public function setAnonymousUser($user)
    {
        $this->anonymousUser = $user;

        return $this;
    }

    /**
     * Set the ID to use as OwnerID for the currently
     * authenticated user/client.
     *
     * @param string $ownerId
     * @return DataService
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * Get a list containing data.
     *
     * @param  integer|null $offset  Offet to start returning data from.
     * @param  integer|null $limit   Amount of data entries to return.
     * @param  string|null  $ownerId Owner ID. If left blank, the owner will be
     *                               inferred from the authenticated user/client.
     * @return array<mixed>          [description]
     */
    public function cgetData($offset = null, $limit = null, $ownerId = null)
    {
        $ownerId = $this->getOwnerId($ownerId);
        $query = [];

        $entities = $this->documentManager->find(
            $this->collectionName,
            $query,
            null,
            null,
            $offset,
            $limit
        );

        return $entities;
    }

    /**
     * Get data.
     *
     * @param  string      $id      Persistent identifier (PID) of the data.
     * @param  string      $ownerId Optional owner of the data. If left blank, the owner will be
     *                              inferred from the authenticated user/client.
     * @return mixed                [description]
     */
    public function getData($id, $ownerId = null)
    {
        $ownerId = $this->getOwnerId($ownerId);

        $query = [
            'data_pids' => $id
        ];

        $entity = $this->documentManager->findOne(
            $this->collectionName,
            $query
        );

        return $entity;
    }

    /**
     * Create data.
     *
     * @param  array       $dataPids   Array of Data PID's
     * @param  array       $objectPids Array of Object PID's
     * @param  string      $schema     Schema of the data.
     * @param  mixed       $data       Data to create
     * @param  string      $raw        Optional raw data in original format
     * @param  string      $ownerId    Optional owner of the data. If ommited or left blank, the owner will be
     *                                 inferred from the authenticated user/client.
     * @return mixed                   Id of the created data if available, other true upon success
     */
    public function createData($dataPids, $objectPids, $schema, $data, $raw = null, $ownerId = null)
    {
        $ownerId = $this->getOwnerID($ownerId);

        $data = [
            'owner'       => $ownerId,
            'created'     => date('c'),
            'schema'      => $schema,
            'data_pids'   => $dataPids,
            'object_pids' => $objectPids,
            'data'        => $data,
            'raw'         => $raw,
        ];

        return $this->documentManager->insert($this->collectionName, $data);
    }

    /**
     * Update data.
     *
     * @param  string      $id         Persistent identifier (PID) of the data to be updated
     * @param  array       $dataPids   Array of Data PID's
     * @param  array       $objectPids Array of Object PID's
     * @param  string      $schema     Schema of the data.
     * @param  mixed       $data       Actual data to use for the operation.
     * @param  string      $raw        Optional raw data in original format
     * @param  string      $ownerId    Optional owner of the data. If left blank, the owner will be
     *                                 inferred from the authenticated user/client.
     * @return mixed                   Updated data.
     */
    public function updateData($id, $dataPids, $objectPids, $schema, $data, $raw = null, $ownerId = null)
    {
        $ownerId = $this->getOwnerID($ownerId);

        $query = [
            'data_pids' => $id,
            'owner'     => $ownerId,
        ];

        $changeset = [
            '$set' => [
                'modified'    => date('c'),
                'schema'      => $schema,
                'data_pids'   => $dataPids,
                'object_pids' => $objectPids,
                'data'        => $data,
                'raw'         => $raw,
            ],
        ];

        $this->documentManager->update($this->collectionName, $query, $changeset);

        return $this->getData($id, $ownerId);
    }

    /**
     * Delete data.
     *
     * @param  string      $id      Primary identifier (PID) of the data.
     * @param  string      $ownerId Optional owner of the data. If left blank, the owner will be
     *                              inferred from the authenticated user/client.
     *
     * @return boolean              Boolean indicating whether or not the operation succeeded.
     */
    public function deleteData($id, $ownerId = null)
    {
        $ownerId = $this->getOwnerID($ownerId);
        $query = [
            'data_pids' => $id,
            'owner'     => $ownerId,
        ];

        $collection = $this->documentManager->getCollection($this->collectionName);
        $result = $collection->remove($query);
        return $result;
//        return $this->documentManager->remove($query);
//        return $this->documentManager->remove($this->collectionName, $query);
    }

    /**
     * Get the ownerId to use for lookups and updates.
     * If the ownerId is not provided to this method,
     * it will be retrieved from the class instance.
     * If it cannot be retrieved, an exception will be
     * thrown.
     *
     * @param  string|null $ownerId
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getOwnerId($ownerId = null)
    {
        if (!$ownerId) {
            $ownerId = $this->ownerId;
        }

        if (!$ownerId) {
            if ($this->anonymousUser !== null) {
                $ownerId = $this->anonymousUser;
            } else
                throw new \InvalidArgumentException('The `ownerId` value was not able to be determined');
        }

        return $ownerId;
    }
}
