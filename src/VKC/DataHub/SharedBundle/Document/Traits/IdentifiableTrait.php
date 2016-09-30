<?php

namespace VKC\DataHub\SharedBundle\Document\Traits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("all")
 */
trait IdentifiableTrait
{
    /**
     * @var string $id
     *
     * @ODM\Id
     *
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     */
    protected $id;

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Determine and return the class identifier.
     *
     * @return string
     */
    public static function getClassIdentifier()
    {
        $type = explode('\\', static::class);
        $type = end($type);

        return $type;
    }
}
