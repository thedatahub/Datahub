<?php

namespace DataHub\ResourceAPIBundle\Document;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * ODM Record document repository class
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceAPIBundle
 *
 * @ODM\Document(collection="Records", repositoryClass="DataHub\ResourceAPIBundle\Repository\RecordRepository")
 * @ODM\HasLifecycleCallbacks
 * @Serializer\XmlRoot("record")
 * @Hateoas\Relation("self",
 *     href = "expr('/api/v1/data/' ~ object.getUrlEncodedPrimaryRecordId())",
 *     exclusion = @Hateoas\Exclusion(groups={"json", "xml"})
 * )
 */
class Record
{
    /**
     * @ODM\Id
     */
    protected $id;

    /** @ODM\Field(type="string") @ODM\Index */
    // protected $owner;

    /** @ODM\Date */
    protected $created;

    /** @ODM\Date */
    protected $updated;

    /**
     * @ODM\Field(type="string")
     * @Serializer\Groups({"json"})
     */
    protected $json;

    /**
     * @ODM\Field(type="string")
     * @Serializer\Groups({"xml"})
     */
    protected $raw;

    /** @ODM\Field(type="collection") */
    protected $recordIds;

    /** @ODM\Field(type="collection") */
    protected $objectIds;

    /**
     * @ODM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime();
        $this->updated = new DateTime();
    }

    /**
     * @ODM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new DateTime();
    }

    /**
     * @ODM\PostLoad
     */
    public function onPostLoad()
    {
        $this->json = json_decode($this->json, true);
    }

    public function getRaw() {
        return $this->raw;
    }

    public function setRaw($raw) {
        $this->raw = $raw;
    }

    public function getJson() {
        return $this->json;
    }

    public function setJson($json) {
        $this->json = $json;
    }

    public function getId() {
        return $this->id;
    }

    public function getRecordIds() {
        return $this->recordIds;
    }

    public function getUrlEncodedPrimaryRecordId() {
        return urlencode($this->recordIds[0]);
    }

    public function setRecordIds($ids) {
        $this->recordIds = $ids;
    }

    public function getObjectIds() {
        return $this->objectIds;
    }

    public function setObjectIds($ids) {
        $this->objectIds = $ids;
    }
}
