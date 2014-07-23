<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StatesLeaveRequestsRepository extends EntityRepository
{
    /**
     * Get the current status of a travel request
     * 
     * @param integer $lrId leave request id
     * @return null|Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequests
     */
    public function getCurrentStatus($lrId)
    {
        $leaveRequestState = $this->createQueryBuilder('lr')
            ->where('lr.leaveRequest = :lrId')
            ->setParameter(':lrId', $lrId)
            ->add('orderBy', 'lr.id DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $leaveRequestState->getOneOrNullResult();
    }    
}
