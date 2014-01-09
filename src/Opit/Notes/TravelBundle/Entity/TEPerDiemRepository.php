<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TEPerDiemRepository extends EntityRepository
{
    public function findAmountToPay($hours)
    {
        $dq = $this->createQueryBuilder('pd')
                ->where("pd.hours <= (:hours)")
                ->orderBy('pd.id', 'ASC')
                ->setParameter(':hours', $hours)
                ->getQuery();
                
        $result = $dq->getResult();

        if (0 == count($result)) {
            $result = 0;
        } else {
            $result = $result[count($result)-1]->getAmmount();
        }
        
        return $result;
    }
}
