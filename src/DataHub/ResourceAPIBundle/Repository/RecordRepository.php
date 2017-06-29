<?php

namespace DataHub\ResourceAPIBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class RecordRepository extends DocumentRepository
{
    /**
     * @param string $field
     * @param string $data
     *
     * @return array|null|object
     */
    public function findOneByProperty($field, $data)
    {
        return
            $this->createQueryBuilder('Record')
                ->field($field)->equals($data)
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
     *
     */
    public function count() {
        return
            $this->createQueryBuilder('Record')
                ->count()
                ->getQuery()
                ->execute();
    }

}
