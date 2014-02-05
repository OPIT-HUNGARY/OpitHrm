<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of StatesTravelRequestsRepository
 *
 * @author OPIT\kaufmann
 */
class StatesTravelRequestsRepository extends EntityRepository
{
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
    
    public function getStatusBeforeLast($trId)
    {
        $travelRequestState = $this->createQueryBuilder('tr')
            ->where('tr.travelRequest = :trId')
            ->setParameter(':trId', $trId)
            ->add('orderBy', 'tr.id DESC')
            ->setMaxResults(2)
            ->getQuery();
        
        return $travelRequestState->getResult()[1];
    }
}
