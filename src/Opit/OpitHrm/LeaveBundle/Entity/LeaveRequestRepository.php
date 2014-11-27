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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\UserBundle\Entity\User;

/**
 * Description of LeaveRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveRequestRepository extends EntityRepository
{
    /**
     * Find all employee leave request with in a date range
     *
     * @param array $employeeIds
     * @param string $startDate
     * @param string $endDate
     * @param array $status
     * @return array
     */
    public function findEmployeesLeaveRequests($employeeIds, $startDate = '', $endDate = '', $status = null)
    {
        $dq = $this->createQueryBuilder('lr');
        $dq->select('lr, l, e')
            ->where('lr.employee in (:employee)')
            // where leave start date or leave end date in the given time frame
            // or leave start date before time frame start and leave end date after time frame end
            ->andWhere(
                $dq->expr()->orX(
                    'l.startDate BETWEEN :startDate AND :endDate',
                    'l.endDate BETWEEN :startDate AND :endDate',
                    $dq->expr()->andX(
                        'l.startDate < :startDate',
                        'l.endDate > :endDate'

                    )
                )
            );
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
     * Find employees leave requests
     *
     * @param User $generalManager
     * @param mixed $states
     * @param array $paginationParameters
     * @param array $parameters
     * @return Paginator
     */
    public function findEmployeeLeaveRequests(User $generalManager, $states, array $paginationParameters, $parameters)
    {
        $dq = $this->createQueryBuilder('lr');

        $dq->select('lr')
            ->where(
                $dq->expr()->eq('lr.generalManager', '(:generalManager)')
            )
            ->andWhere(
                $dq->expr()->neq('lr.employee', $generalManager->getEmployee()->getId())
            )
            ->andWhere(
                $dq->expr()->isNull('lr.leaveRequestGroup')
            )
            ->andWhere(
                $dq->expr()->in('s.status', ':states')
            )
            ->innerJoin('lr.states', 's')
            ->setParameter(':states', $states)
            ->setParameter('generalManager', $generalManager->getId());

        $dq = $this->findByParams($parameters, $dq);

        $dq->setFirstResult($paginationParameters['firstResult']);
        $dq->setMaxResults($paginationParameters['maxResults']);

        return new Paginator($dq->getQuery(), true);
    }

    /**
     * Find mass leave requests assigned to gm
     *
     * @param User $generalManager
     * @param array $paginationParameters
     * @param array $parameters
     * @return Paginator
     */
    public function findMassLeaveRequests(User $generalManager, array $paginationParameters, array $parameters)
    {
        $dq = $this->createQueryBuilder('lr');

        $dq->select('lr')
            ->where(
                $dq->expr()->eq('lr.generalManager', '(:generalManager)')
            )
            ->andWhere(
                $dq->expr()->isNotNull('lr.leaveRequestGroup')
            )
            ->andWhere(
                $dq->expr()->eq('lr.generalManager', $generalManager->getId())
            )
            ->setParameter('generalManager', $generalManager->getId());

        $dq = $this->findByParams($parameters, $dq);

        $dq->setFirstResult($paginationParameters['firstResult']);
        $dq->setMaxResults($paginationParameters['maxResults']);

        return new Paginator($dq->getQuery(), true);
    }

    /**
     * Find own leave requests
     *
     * @param integer $employeeId
     * @param array $paginationParameters
     * @param array $parameters
     * @return Paginator
     */
    public function findOwnLeaveRequests($employeeId, array $paginationParameters, array $parameters)
    {
        $dq = $this->createQueryBuilder('lr');

        $dq->select('lr')
            ->where(
                $dq->expr()->eq('lr.employee', '(:employeeId)')
            )
            ->andWhere(
                $dq->expr()->eq('lr.isMassLeaveRequest', 0)
            )
            ->setParameter('employeeId', $employeeId);

        $dq = $this->findByParams($parameters, $dq);

        $dq->setFirstResult($paginationParameters['firstResult']);
        $dq->setMaxResults($paginationParameters['maxResults']);

        return new Paginator($dq->getQuery(), true);
    }

    /**
     * @param array $parameters
     * @param \Doctrine\ORM\QueryBuilder $dq
     * @return mixed
     */
    public function findByParams($parameters, $dq)
    {
        $dq->innerJoin('lr.leaves', 'l')
            ->innerJoin('lr.employee', 'e')
            ->innerJoin('e.user', 'u');

        $orderParams = isset($parameters['order']) ? $parameters['order'] : array(
            'field' => 'lr.id',
            'dir' => 'ASC'
        );

        if (isset($parameters['search'])) {
            $searchParams = $parameters['search'];

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
                $dq->setParameter(':email', '%' . $searchParams['email'] . '%');
            }
            if (isset($searchParams['employeeName']) && $searchParams['employeeName'] !== '') {
                $dq->andWhere('e.employeeName LIKE :employeeName');
                $dq->setParameter(':employeeName', '%' . $searchParams['employeeName'] . '%');
            }

            if (isset($searchParams['leaveId']) && $searchParams['leaveId'] !== '') {
                $dq->andWhere('lr.leaveRequestId LIKE :leaveId');
                $dq->setParameter(':leaveId', '%' . $searchParams['leaveId'] . '%');
            }
        }

        $dq->addOrderBy($orderParams['field'], $orderParams['dir']);

        return $dq;
    }

    /**
     * Find all approved leave requests by date interval.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function findApprovedLeaveRequestsByDates($startDate = '', $endDate = '')
    {
        $dq = $this->createQueryBuilder('lr');
        $dq->select('lr, l, e')
            ->where(
                // Check if lr leave starts or ends between start and end date
                $dq->expr()->orX(
                    'l.startDate BETWEEN :startDate AND :endDate',
                    'l.endDate BETWEEN :startDate AND :endDate'
                )
            )
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
     * @param integer $employeeId
     * @param string $startDate
     * @param string $endDate
     * @param boolean $finalizedOnly , returns total request count by default
     * @return mixed
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
     * @return mixed
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
