<?php

namespace BackendBundle\Repository;

use BackendBundle\Entity\Columna;
use Doctrine\ORM\EntityRepository;

/**
 * ColumnRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ColumnRepository extends EntityRepository {

    public function  findByAuthorId($author_id){
        $result = $this->createQueryBuilder('p')
                ->select('p')
                ->innerJoin('p.authors', 'a')
                ->where('a.id = :id')
                ->setParameter('id', $author_id)
                ->getQuery()->getResult();
        return $result;
    }
}
