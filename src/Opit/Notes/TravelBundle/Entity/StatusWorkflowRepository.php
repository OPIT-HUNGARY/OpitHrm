<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Opit\Notes\TravelBundle\Entity\Status;

class StatusWorkflowRepository extends EntityRepository
{
    public function findAvailableStates(Status $parent, $excludes = array())
    {
        $dq = $this->createQueryBuilder('sw');
        
        $dq->where("sw.parent = :parent")
            ->setParameter(':parent', $parent);
        
        if ($excludes) {
            $dq->andWhere(
                $dq->expr()->notIn('sw.status', $excludes)
            );
        }
   
        return $dq->getQuery()->getResult();
    }
}
