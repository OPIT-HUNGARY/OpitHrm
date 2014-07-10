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
        $orderParams = isset($parameters['order']) ? $parameters['order'] : array(
            'field' => 'lr.id',
            'dir' => 'ASC'
        );
        $searchParams = isset($parameters['search']) ? $parameters['search'] : array();

        $dq = $this->createQueryBuilder('lr')
            ->innerJoin('lr.leaves', 'l')
            ->innerJoin('lr.employee', 'e')
            ->innerJoin('e.user', 'u');

        if (isset($searchParams['startDate']) && $searchParams['startDate'] !== '') {
            $dq->andWhere('l.startDate >= :startDate');
            $dq->setParameter(':startDate', $searchParams['startDate']);
        }

        if (isset($searchParams['endDate']) && $searchParams['endDate'] !== '') {
            $dq->andWhere('l.endDate <= :endDate');
            $dq->setParameter(':endDate', $searchParams['endDate']);
        }

        if (isset($searchParams['email']) && $searchParams['email'] !== '') {
            $dq->andWhere('u.email LIKE :email');
            $dq->setParameter(':email', '%' . $searchParams['email']. '%');
        }
        if (isset($searchParams['employeeName']) && $searchParams['employeeName'] !== '') {
            $dq->andWhere('e.employeeName LIKE :employeeName');
            $dq->setParameter(':employeeName', '%' . $searchParams['employeeName']. '%');
        }

        if (isset($searchParams['leaveId']) && $searchParams['leaveId'] !== '') {
            $dq->andWhere('lr.leaveRequestId LIKE :leaveId');
            $dq->setParameter(':leaveId', '%'.$searchParams['leaveId'].'%');
        }

        if (!$pagnationParameters['isAdmin']) {
            if ($pagnationParameters['isGeneralManager']) {
                $statusExpr = $dq->expr()->orX(
                    $dq->expr()->andX(
                        $dq->expr()->orX(
                            $dq->expr()->notIn('s.status', ':status'), $dq->expr()->eq('lr.isMassLeaveRequest', 1)
                        ), $dq->expr()->eq('lr.generalManager', ':user')
                    ), $dq->expr()->eq('lr.employee', ':employee')
                );
                $dq->leftJoin('lr.states', 's', 'WITH')
                    ->andWhere($statusExpr);

                $dq->setParameter(':user', $pagnationParameters['user']);
                $dq->setParameter(':status', Status::CREATED);
                $dq->setParameter(':employee', $pagnationParameters['employee']);
            } elseif (!$pagnationParameters['isGeneralManager']) {
                $dq->andWhere($dq->expr()->eq('lr.employee', ':employee'));
                $dq->setParameter(':employee', $pagnationParameters['employee']);
            }
        }

        // Order the result, mass leave request needs to be opposite of grouping
        $dq->addOrderBy('lr.leaveRequestGroup', $orderParams['dir'])
            ->addOrderBy('lr.isMassLeaveRequest', (strtoupper($orderParams['dir']) == 'ASC') ? 'DESC' : 'ASC')
            ->addOrderBy($orderParams['field'], $orderParams['dir']);

        $dq->setFirstResult($pagnationParameters['firstResult']);
        $dq->setMaxResults($pagnationParameters['maxResults']);

        return new Paginator($dq->getQuery(), true);
    }

    /**
     * Find all employee leave request with in a date range
     *
     * @param type $employeeIds
     * @param type $startDate
     * @param type $endDate
     * @param type $status
     * @return type
     */
    public function findEmployeesLeaveRequests($employeeIds, $startDate = '', $endDate = '', $status = null)
    {
        $dq = $this->createQueryBuilder('lr');
        $dq->select('lr, l, e')
                ->where('lr.employee in (:employee)')
                ->andwhere('l.startDate > :startDate')
                ->andWhere('l.endDate < :endDate');
        if ($status) {
            $dq->andWhere($dq->expr()->In('s.status', ':states'));
            $dq->setParameter(':states', $status);
            $dq->innerJoin('lr.states', 's');
        }
        $dq->innerJoin('lr.leaves', 'l')
                ->innerJoin('lr.employee', 'e')
                ->setParameter('employee', $employeeIds)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        $q = $dq->getQuery();

        return $q->getResult();
    }

    /**
     * Find all approved leave requests by date interval.
     *
     * @param date $startDate
     * @param date $endDate
     * @return array
     */
    public function findApprovedLeaveRequestsByDates($startDate = '', $endDate = '')
    {
        $dq = $this->createQueryBuilder('lr');
        $dq->select('lr, l, e')
            ->where($dq->expr()->gte('l.startDate', ':startDate'))
            ->andWhere($dq->expr()->lte('l.endDate', ':endDate'))
            ->andWhere($dq->expr()->eq('s.status', ':status'))
            ->innerJoin('lr.leaves', 'l')
            ->innerJoin('lr.employee', 'e')
            ->innerJoin('lr.states', 's')
            ->setParameter('status', Status::APPROVED)
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
        $dq->select('count(distinct lr)')
                ->where('lr.employee = :employee')
                ->andwhere('l.startDate > :startDate')
                ->andWhere('l.endDate < :endDate')
                ->andWhere('lr.isMassLeaveRequest = :isMassLeaveRequest');
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
                ->setParameter('endDate', $endDate)
                ->setParameter('isMassLeaveRequest', 0);
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
                ->where('lr.employee = :employee')
                ->andWhere('lr.isMassLeaveRequest = :isMassLeaveRequest')
                ->innerJoin('lr.leaves', 'l');
        if (!$incNonAnnualEntLeaves) {
            $dq->andWhere($dq->expr()->eq('c.isCountedAsLeave', ':countAsLeave'))
                    ->setParameter('countAsLeave', 1)
                    ->innerJoin('l.category', 'c');
        }
        $dq->setParameter('employee', $employeeId)
                ->setParameter('isMassLeaveRequest', 0);

        if (!empty($rejectedLeaveRequestIds)) {
            $dq->andWhere($dq->expr()->notIn('lr.id', ':lrIds'));
            $dq->setParameter('lrIds', $rejectedLeaveRequestIds);
        }

        return $dq->getQuery()->getSingleScalarResult();
    }

}
