<?php

namespace DataHub\SetBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * ODM Set document repository class
 *
 * @author Michiel Hebben
 * @package DataHub\SetBundle
 */
class SetRepository extends DocumentRepository
{
    /**
     * Find one set by field and value
     *
     * @param string $field
     * @param string $value
     *
     * @return array|null|object
     */
    public function findOneByProperty($field, $value)
    {
        return
            $this->createQueryBuilder('Set')
                ->field($field)->equals($value)
                ->getQuery()
                ->getSingleResult();
    }

    /**
     * Find sets between $from and $until times.
     *
     * This function will return a set of sets based on the 'updated' property.
     * This means the returned set contains sets which were updated between $from
     * and until.
     *
     * @param mixed $from The date from (timestamp, DateTime or MongoDate)
     * @param mixed $until The date until (timestamp, DateTime or MongoDate)
     * @param int $limit
     * @param int $offset
     *
     * @return array of nodes
     */
    public function findByBetweenFromUntil($from = null, $until = null, $limit = null, $offset = null, $count = false)
    {
        $builder = $this->createQueryBuilder('Set');

        if (!is_null($from) && is_null($until)) {
            $builder->field('updated')->gte($from);
        }

        if (is_null($from) && !is_null($until)) {
            $builder->field('updated')->lte($until);
        }

        if (!is_null($from) && !is_null($until)) {
            $builder
                ->field('updated')->gte($from)
                ->field('updated')->lte($until);
        }

        if (!is_null($limit)) {
            $builder = $builder->limit($limit);
        }

        if (!is_null($offset)) {
            $builder = $builder->skip($offset);
        }

        if ($count) {
            $builder = $builder->count();
        }

        return $builder->getQuery()->execute();
    }

    /**
     * Return the number of stored sets.
     *
     * @return int
     */
    public function count()
    {
        return
            $this->createQueryBuilder('Set')
                ->count()
                ->getQuery()
                ->execute();
    }

}
