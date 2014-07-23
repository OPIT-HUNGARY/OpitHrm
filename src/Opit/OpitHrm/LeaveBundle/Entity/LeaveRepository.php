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
use Opit\OpitHrm\StatusBundle\Entity\Status;

/**
 * Description of LeaveRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveRepository extends EntityRepository
{
    /**
     * Find all leaves that are overlapping start and end date, related LRs employee is in $employees
     * 
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array $employees
     * @return type
     */
    public function findOverlappingLeavesByDatesEmployees(\DateTime $startDate, \DateTime$endDate, array $employees)
    {   
        // fetch all leave requests that have been rejected
        $q = $this->createQueryBuilder('l');
        $q->select('lr.id')
            ->where($q->expr()->eq('s.status', ':status'))
            ->innerJoin('l.leaveRequest', 'lr')
            ->innerJoin('lr.states', 's')
            ->setParameter('status', Status::REJECTED);

        $rejectedLeaveRequestIds = $q->getQuery()->getScalarResult();
        
        $dq = $this->createQueryBuilder('l');
        $dq->select('l')
            ->where($dq->expr()->eq('lr.isMassLeaveRequest', ':isMassLeaveRequest'))
            ->andWhere($dq->expr()->in('e.id', ':employees'))
            ->andWhere($dq->expr()->gte('l.endDate', ':startDate'))
            ->andWhere($dq->expr()->lte('l.startDate', ':endDate'))
            ->andWhere($dq->expr()->notIn('lr.id', ':lrIds'))
            ->innerJoin('l.leaveRequest', 'lr')
            ->innerJoin('lr.employee', 'e')
            ->setParameter('isMassLeaveRequest', 0)
            ->setParameter('employees', $employees)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            // if $rejectedLeaveRequestIds is empty no results are returned
            ->setParameter('lrIds', $rejectedLeaveRequestIds ? $rejectedLeaveRequestIds : array(0));
        
        return $dq->getQuery()->getResult();
    }
}
