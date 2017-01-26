<?php

namespace DataHub\SharedBundle\Document\Traits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("all")
 */
trait LabelableTrait
{
    /**
     * @var string $label
     *
     * @ODM\Field(type="string")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"global", "list", "single"})
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $label;

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param  string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}
