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
    public function findByfindBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL) {
        $builder = $this->createQueryBuilder('Record');

        if (!is_null($limit)) {
            $builder = $builder->limit($limit);
        }

        if (!is_null($offset)) {
            $builder = $builder->skip($offset);
        }

        return $builder->getQuery->execute();
    }

    /**
     * Return the number of stored records.
     *
     * @return int
     */
    public function count() {
        return
            $this->createQueryBuilder('Record')
                ->count()
                ->getQuery()
                ->execute();
    }

}
