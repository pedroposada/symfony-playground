<?php

namespace PSL\ClipperBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use PSL\ClipperBundle\Entity\FirstQProject;

/**
 * FirstQProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FirstQProjectRepository extends EntityRepository
{
  public function getLatestFQs($limit = null)
  {
    $qb = $this->createQueryBuilder('fq')
               ->select('fq')
               ->addOrderBy('fq.created', 'DESC');

    if (!is_null($limit)) {
      $qb->setMaxResults($limit);
    }

    return $qb->getQuery()->getResult();
  }

}
