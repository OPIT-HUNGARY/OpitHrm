<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of StatesTravelRequestsRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class StatesTravelRequestsRepository extends EntityRepository
{
    /**
     * Get the current status of a travel request
     *
     * @param integer $trId travel request id
     * @return null|Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests
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
     * @return Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests
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

    /**
     * Find a travel request's status by status id and travel request id
     *
     * @param integer $trId travel request id
     * @param integer $statusId status id
     * @return null|Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests
     */
    public function findStatusByStatusId($trId, $statusId)
    {
        $travelRequestState = $this->createQueryBuilder('tr')
            ->where('tr.travelRequest = :trId')
            ->andWhere('tr.status = :statusId')
            ->setParameter(':trId', $trId)
            ->setParameter(':statusId', $statusId)
            ->setMaxResults(1)
            ->getQuery();

        return $travelRequestState->getOneOrNullResult();
    }
}
