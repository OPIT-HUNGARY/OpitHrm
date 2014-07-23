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

/**
 * Description of LeaveDateRepository
 */
class LeaveDateRepository extends EntityRepository
{
    /**
     * Find all leave dates by parameters
     *
     * @param array $searchProperties
     * @return array \Opit\OpitHrm\LeaveBundle\Entity\LeaveDate objects
     */
    public function findAllFiltered($searchProperties = array())
    {
        $startYear = $endYear = date('Y');
        $startMonth = 1;
        $endMonth = 12;
        $orderParams = isset($searchProperties['order']) ? $searchProperties['order'] : array();
        $searchParams = isset($searchProperties['search']) ? $searchProperties['search'] : array();

        $qb = $this->createQueryBuilder('ld');
        // Get the date range
        if (isset($searchParams['year'])) {
            sort($searchParams['year'], SORT_NUMERIC);
            $startYear = reset($searchParams['year']);
            $endYear = end($searchParams['year']);
        }
        if (isset($searchParams['month'])) {
            sort($searchParams['month'], SORT_NUMERIC);
            $startMonth = reset($searchParams['month']);
            $endMonth = end($searchParams['month']);
        }

        // Set the first day of the filtered date.
        $startDate = new \DateTime();
        $startDate->setDate($startYear, $startMonth, 01);
        // Set the last day of the filtered date.
        $endDate = new \DateTime();
        $endDate->setDate($endYear, $endMonth, 31);

        // Set the parameters.
        $parameters = array(
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        );

        $qb->where($qb->expr()->gte('ld.holidayDate', ':startDate'))
            ->andWhere($qb->expr()->lte('ld.holidayDate', ':endDate'));

        if (isset($searchParams['type'])) {
            $qb->andWhere(
                $qb->expr()->in('ld.holidayType', $searchParams['type'])
            );
        }
        $qb->setParameters($parameters);

        if (isset($orderParams['field']) && $orderParams['field'] && isset($orderParams['dir']) && $orderParams['dir']) {
            $qb->orderBy('ld.'.$orderParams['field'], $orderParams['dir']);
        } else {
            $qb->orderBy('ld.holidayDate', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get the all avaliable years in the leave dates.
     *
     * @return array of years
     */
    public function findAllYears()
    {
        $leaveDate = $this->createQueryBuilder('ld');
        $leaveDate
            ->select('SUBSTRING(ld.holidayDate, 1,4) as year')
            ->distinct()
            ->orderBy('ld.holidayDate', 'DESC');

        $queryResult = $leaveDate->getQuery()->getResult();

        $result = array();

        foreach($queryResult as $qr) {
            $result[] = $qr['year'];
        }

        return $result;
    }

    /**
     * Count leave working days between date range
     *
     * @param Datetime $startDate
     * @param Datetime $endDate
     * @param integer $category
     * @return type
     */
    public function countLWDBWDateRange($startDate, $endDate, $category)
    {
        $qb = $this->createQueryBuilder('ld');
        $qb->select('count(ld.id)')
            ->where($qb->expr()->gte('ld.holidayDate', ':startDate'))
            ->andWhere($qb->expr()->lte('ld.holidayDate', ':endDate'))
            ->innerJoin('ld.holidayType', 'lc')
            ->andWhere($qb->expr()->eq('lc.isWorkingDay', ($category ? '1': '0')));
        $qb->setParameters(
            array(
                'startDate' => $startDate,
                'endDate' => $endDate,
            )
        );

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get administrative leave between dates
     *
     * @param Datetime $startDate
     * @param Datetime $endDate
     * @return type
     */
    public function getAdminLeavesInDateRange($startDate, $endDate)
    {
        $qb = $this->createQueryBuilder('ld');
        $qb->select('ld.holidayDate')
            ->where($qb->expr()->gte('ld.holidayDate', ':startDate'))
            ->andWhere($qb->expr()->lte('ld.holidayDate', ':endDate'))
            ->innerJoin('ld.holidayType', 'lc')
            ->andWhere($qb->expr()->eq('lc.isWorkingDay', '0'));
        $qb->setParameters(
            array(
                'startDate' => $startDate,
                'endDate' => $endDate,
            )
        );

        return $qb->getQuery()->getResult();
    }
}
