<?php

namespace VKC\DataHub\ResourceBundle\Service;

use VKC\DataHub\SharedBundle\Service\DocumentsService;

/**
 * DataService is a service for managing work data resources.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class DataService
{
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
     * @param  string      $id_path    Path of the identifier within the data document
     * @param  string      $id      Persistent identifier (PID) of the data.
     * @param  string      $ownerId   Optional owner of the data. If left blank, the owner will be
     *                              inferred from the authenticated user/client.
     * @return mixed                [description]
     */
    public function getData($id_path, $id, $ownerId = null)
    {
        $ownerId = $this->getOwnerId($ownerId);

        $query = [
            $id_path => $id
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
     * @param  mixed       $data    Data to create
     * @param  string      $schema  Schema of the data.
     * @param  string      $raw     Optional raw data in original format
     * @param  string      $ownerId   Optional owner of the data. If ommited or left blank, the owner will be
     *                              inferred from the authenticated user/client.
     * @return mixed                Created data.
     */
    public function createData($data, $schema, $raw = null, $ownerId = null)
    {
        $ownerId = $this->getOwnerID($ownerId);

        $data = [
            'owner'   => $ownerId,
            'created' => date('c'),
            'schema'  => $schema,
            'data'    => $data,
            'raw'     => $raw,
        ];

        $collection = $this->documentManager->getCollection($this->collectionName);
        $collection->insert($data);

        $entity = $data;

        return $data;
    }

    /**
     * Update data.
     *
     * @param  string      $id_path    Path of the identifier within the data document
     * @param  string      $id      Persistent identifier (PID) of the data.
     * @param  mixed       $data    Actual data to use for the operation.
     * @param  string      $schema  Schema of the data.
     *
     * @param  string      $raw     Optional raw data in original format
     * @param  string      $ownerId   Optional owner of the data. If left blank, the owner will be
     *                              inferred from the authenticated user/client.
     * @return mixed                Updated data.
     */
    public function updateData($id_path, $id, $data, $schema, $raw = null, $ownerId = null)
    {
        $ownerId = $this->getOwnerID($ownerId);
        $query = [
            $id_path     => $id,
            'owner'   => $ownerId,
        ];

        $changeset = [
            'modified' => date('c'),
            'schema'   => $schema,
            'data'     => $data,
            'raw'      => $raw,
        ];

        $collection = $this->documentManager->getCollection($this->collectionName);
        $collection->update($query, $changeset);

        return $this->getData($id_path, $id, $ownerId);
    }

    /**
     * Delete data.
     *
     * @param  string      $id_path    Path of the identifier within the data document
     * @param  string      $id      Primary identifier (PID) of the data.
     * @param  string      $ownerId   Optional owner of the data. If left blank, the owner will be
     *                              inferred from the authenticated user/client.
     * @return boolean              Boolean indicating whether or not the operation
     *                              succeeded.
     */
    public function deleteData($id_path, $id, $ownerId = null)
    {
        $ownerId = $this->getOwnerID($ownerId);
        $query = [
            $id_path     => $id,
            'owner'   => $ownerId,
        ];

        $collection = $this->documentManager->getCollection($this->collectionName);
        $result = $collection->remove($query);

        return $result['n'] > 0;
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
            throw new \InvalidArgumentException('The `ownerId` value was not able to be determined');
        }

        return $ownerId;
    }
}
