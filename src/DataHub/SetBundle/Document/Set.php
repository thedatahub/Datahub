<?php

namespace DataHub\SetBundle\Document;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * ODM Set document repository class
 *
 * @author Michiel Hebben
 * @package DataHub\SetBundle
 *
 * @ODM\Document(collection="Sets", repositoryClass="DataHub\SetBundle\Repository\SetRepository")
 * @Serializer\XmlRoot("set")
 *
 */
class Set
{
    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * @var \DateTime $created
     *
     * @ODM\Field(type="date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

    /**
     * @var \DateTime $updated
     *
     * @ODM\Field(type="date")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @ODM\Field(type="string")
     */
    protected $spec;

    /**
     * @ODM\Field(type="string")
     */
    protected $name;

    public function getUpdated()
    {
        return $this->updated;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSpec()
    {
        return $this->spec;
    }

    public function setSpec($spec)
    {
        $this->spec = $spec;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
