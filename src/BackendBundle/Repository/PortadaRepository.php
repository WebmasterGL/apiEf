<?php

namespace BackendBundle\Repository;

/**
 * PortadaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PortadaRepository extends \Doctrine\ORM\EntityRepository
{
    public function getPortada($idportada){


        $em = $this->getEntityManager();


        $dql = "select p, f   from BackendBundle:PortadaFolds f join f.idportada p where p.id=:idportada AND f.status <> 'trash' order by f.orden ASC";

        $query = $em->createQuery($dql)
                ->setParameter('idportada', $idportada);

        $portada = $query->getResult();

        return $portada;


    }
}
