<?php

namespace DataHub\ResourceAPIBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * ODM Record document repository class
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceAPIBundle
 */
class RecordRepository extends DocumentRepository
{
    /**
     * Find one record by field and value
     *
     * @param string $field
     * @param string $value
     *
     * @return array|null|object
     */
    public function findOneByProperty($field, $value)
    {
        return
            $this->createQueryBuilder('Record')
                ->field($field)->equals($value)
                ->getQuery()
                ->getSingleResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByfindBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
    {
        $builder = $this->createQueryBuilder('Record');

        if (!is_null($limit)) {
            $builder = $builder->limit($limit);
        }

        if (!is_null($offset)) {
            $builder = $builder->skip($offset);
        }

        return $builder->getQuery()->execute();
    }

    /**
     * Find records between $from and $until times.
     *
     * This function will return a set of records based on the 'updated' property.
     * This means the returned set contains recors which were updated between $from
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
        $builder = $this->createQueryBuilder('Record');

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
     * Return the number of stored records.
     *
     * @return int
     */
    public function count()
    {
        return
            $this->createQueryBuilder('Record')
                ->count()
                ->getQuery()
                ->execute();
    }

}
