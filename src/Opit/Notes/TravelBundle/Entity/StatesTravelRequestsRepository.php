<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of StatesTravelRequestsRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class StatesTravelRequestsRepository extends EntityRepository
{
    /**
     * Get the current status of a travel request
     * 
     * @param integer $trId travel request id
     * @return null|Opit\Notes\TravelBundle\Entity\StatesTravelRequests
     */
    public function getCurrentStatus($trId)
    {
        $travelRequestState = $this->createQueryBuilder('tr')
            ->where('tr.travelRequest = :trId')
            ->setParameter(':trId', $trId)
            ->add('orderBy', 'tr.id DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $travelRequestState->getOneOrNullResult();
    }
    
    /**
     * Get the second last status of a travel request
     * 
     * @param mixed $id An id or TravelRequest object
     * @return Opit\Notes\TravelBundle\Entity\StatesTravelRequests
     */
    public function getStatusBeforeLast($id)
    {
        $qb = $this->createQueryBuilder('tr')
            ->where('tr.travelRequest = :id')
            ->setParameter(':id', $id)
            ->add('orderBy', 'tr.id DESC')
            ->setMaxResults(2)
            ->getQuery();
        
        $results = $qb->getResult();
        
        return isset($results[1]) ? $results[1] : null;
    }
    
    /**
     * Get the count of the statuses of a travel request
     * 
     * @param mixed $id An id or TravelRequest object
     * @return integer The states count
     */
    public function getStatusCountForTravelRequest($id)
    {
        $qb = $this->createQueryBuilder('tr')
            ->select('COUNT(tr.id)')
            ->where('tr.travelRequest = :id')
            ->setParameter(':id', $id)
            ->getQuery();
        
        return $qb->getSingleScalarResult();
    }
}
