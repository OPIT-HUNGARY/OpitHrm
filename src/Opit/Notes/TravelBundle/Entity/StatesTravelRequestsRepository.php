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
     * Get the penult status of a travel request
     * 
     * @param integer $trId travel request id
     * @return Opit\Notes\TravelBundle\Entity\StatesTravelRequests
     */
    public function getStatusBeforeLast($trId)
    {
        $travelRequestState = $this->createQueryBuilder('tr')
            ->where('tr.travelRequest = :trId')
            ->setParameter(':trId', $trId)
            ->add('orderBy', 'tr.id DESC')
            ->setMaxResults(2)
            ->getQuery();
        
        $results = $travelRequestState->getResult();
        return $results[1];
    }
    
    /**
     * Get the count of the statuses of a travel request
     * 
     * @param integer $trId travel request id
     * @return integer the counted statuses
     */
    public function getStatusCountForTravelRequest($trId)
    {
        $travelRequestState = $this->createQueryBuilder('tr')
            ->where('tr.travelRequest = :trId')
            ->setParameter(':trId', $trId)
            ->getQuery();
        
        return count($travelRequestState->getResult());
    }
}
