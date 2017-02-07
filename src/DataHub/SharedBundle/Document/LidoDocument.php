<?php

namespace DataHub\SharedBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class LidoDocument
{
    /**
     * @ODM\Id
     */
    protected $id;

    /** @ODM\Field(type="string") @ODM\Index */
    protected $owner;

    /** @ODM\Field(type="string") @ODM\Index(unique=true) */
    protected $data_pids;

    /** @ODM\Field(type="string") @ODM\Index */
    protected $object_pids;

}
