<?php

/*
 * The MIT License
 *
 * Copyright 2014 OPIT\bota.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of LeaveDateRepository
 */
class LeaveDateRepository extends EntityRepository
{
    /**
     * Find all leave dates by parameters
     *
     * @param array $searchParams
     * @return array \Opit\Notes\LeaveBundle\Entity\LeaveDate objects
     */
    public function findAllFiltered($searchParams = array())
    {
        $startYear = $endYear = date('Y');
        $startMonth = 1;
        $endMonth = 12;

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

        $qb->setParameters($parameters)
            ->orderBy('ld.holidayDate', 'ASC');

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
}
