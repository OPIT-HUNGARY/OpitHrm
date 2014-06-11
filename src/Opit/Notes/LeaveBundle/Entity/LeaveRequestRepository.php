<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Opit\Notes\StatusBundle\Entity\Status;

/**
 * Description of LeaveRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
class LeaveRequestRepository extends EntityRepository
{
    /**
     * @param array $parameters
     * @return object
     */
    public function findAllByFiltersPaginated($pagnationParameters, $parameters = array())
    {
        $dq = $this->createQueryBuilder('lr')
            ->innerJoin('lr.leaves', 'l')
            ->innerJoin('lr.employee', 'e')
            ->innerJoin('e.user', 'u');

        if (isset($parameters['startDate']) && $parameters['startDate'] !== '') {
            $dq->andWhere('l.startDate >= :startDate');
            $dq->setParameter(':startDate', $parameters['startDate']);
        }

        if (isset($parameters['endDate']) && $parameters['endDate'] !== '') {
            $dq->andWhere('l.endDate <= :endDate');
            $dq->setParameter(':endDate', $parameters['endDate']);
        }
        
        if (isset($parameters['email']) && $parameters['email'] !== '') {
            $dq->andWhere('u.email LIKE :email');
            $dq->setParameter(':email', '%' . $parameters['email']. '%');
        }
        if (isset($parameters['employeeName']) && $parameters['employeeName'] !== '') {
            $dq->andWhere('e.employeeName LIKE :employeeName');
            $dq->setParameter(':employeeName', '%' . $parameters['employeeName']. '%');
        }

        if (isset($parameters['leaveId']) && $parameters['leaveId'] !== '') {
            $dq->andWhere('lr.leaveRequestId LIKE :leaveId');
            $dq->setParameter(':leaveId', '%'.$parameters['leaveId'].'%');
        }
        
        if ($pagnationParameters['isGeneralManager'] || $pagnationParameters['isAdmin']) {
            $statusExpr = $dq->expr()->orX(
                $dq->expr()->andX(
                    $dq->expr()->notIn('s.status', ':status'),
                    $dq->expr()->eq('lr.generalManager', ':user')
                ),
                $dq->expr()->eq('lr.employee', ':employee')
            );
            $dq->leftJoin('lr.states', 's', 'WITH')
                ->andWhere($statusExpr);

            $dq->setParameter(':user', $pagnationParameters['user']);
            $dq->setParameter(':status', Status::CREATED);
            $dq->setParameter(':employee', $pagnationParameters['employee']);
        } elseif (!$pagnationParameters['isGeneralManager'] && !$pagnationParameters['isAdmin']) {
            $dq->andWhere($dq->expr()->eq('lr.employee', ':employee'));
            $dq->setParameter(':employee', $pagnationParameters['employee']);
        }


        $dq->setFirstResult($pagnationParameters['firstResult']);
        $dq->setMaxResults($pagnationParameters['maxResults']);

        return new Paginator($dq->getQuery(), true);
    }

    public function findEmployeesLeaveRequests($employeeIds, $startDate = '', $endDate = '')
    {
        $dq = $this->createQueryBuilder('lr')
            ->select('lr, l, e')
            ->where('lr.employee in (:employee)')
            ->andwhere('l.startDate > :startDate')
            ->andWhere('l.endDate < :endDate')
            ->innerJoin('lr.leaves', 'l')
            ->innerJoin('lr.employee', 'e')
            ->setParameter('employee', $employeeIds)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $dq->getResult();
    }

    /**
     * Find all leave requests by date interval.
     *
     * @param date $startDate
     * @param date $endDate
     * @return array
     */
    public function findLeaveRequestsByDates($startDate = '', $endDate = '')
    {
        $dq = $this->createQueryBuilder('lr');
        $dq->select('lr, l, e')
            ->where($dq->expr()->gte('l.startDate', ':startDate'))
            ->andWhere($dq->expr()->lte('l.endDate', ':endDate'))
            ->innerJoin('lr.leaves', 'l')
            ->innerJoin('lr.employee', 'e')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $dq->getQuery()->getResult();
    }

    /**
     * Total employee leave request
     *
     * @param type $employeeId
     * @param date $startDate
     * @param date $endDate
     * @param boolean $finalizedOnly , returns total request count by default
     * @return type
     */
    public function findEmployeesLRCount($employeeId, $startDate = '', $endDate = '', $finalizedOnly = false)
    {
        $dq = $this->createQueryBuilder('lr');
        $dq->select('count(lr)')
                ->where('lr.employee = :employee')
                ->andwhere('l.startDate > :startDate')
                ->andWhere('l.endDate < :endDate');
        if ($finalizedOnly) {
            $status = array(Status::APPROVED, Status::PAID, Status::REJECTED);
            $dq->andWhere($dq->expr()->In('s.status', ':states'));
            $dq->setParameter(':states', $status);
            $dq->innerJoin('lr.states', 's');
        }
        $dq->innerJoin('lr.leaves', 'l')
                ->innerJoin('lr.employee', 'e')
                ->setParameter('employee', $employeeId)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        $q = $dq->getQuery();

        return $q->getSingleScalarResult();
    }

    /**
     * Summarize number of leave days an employee has took
     * 
     * @param integer $employeeId
     * @param bool $incNonAnnualEntLeaves decides to include or not leaves not to be subtracted from annual leaves
     * @return type
     */
    public function totalCountedLeaveDays($employeeId, $incNonAnnualEntLeaves = false)
    {
        $q = $this->createQueryBuilder('lr');
        $q->select('lr.id')
            ->where($q->expr()->eq('s.status', ':status'))
            ->innerJoin('lr.states', 's')
            ->setParameter('status', Status::REJECTED);

        $rejectedLeaveRequestIds = $q->getQuery()->getScalarResult();

        $dq = $this->createQueryBuilder('lr');
        $dq->select('sum(l.numberOfDays)')
            ->where('lr.employee = :employee');
            if(!$incNonAnnualEntLeaves){
                $dq->andWhere($dq->expr()->eq('c.isCountedAsLeave', ':countAsLeave'))
                ->setParameter('countAsLeave', 1);
            }
            $dq->innerJoin('lr.leaves', 'l')
            ->innerJoin('lr.states', 's')
            ->innerJoin('l.category', 'c')
            ->setParameter('employee', $employeeId);

        if (!empty($rejectedLeaveRequestIds)) {
            $dq->andWhere($dq->expr()->notIn('lr.id', ':lrIds'));
            $dq->setParameter('lrIds', $rejectedLeaveRequestIds);
        }

        return $dq->getQuery()->getSingleScalarResult();
    }
}
