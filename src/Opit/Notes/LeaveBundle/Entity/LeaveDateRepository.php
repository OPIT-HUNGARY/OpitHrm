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
     * Find all leave dates by year
     * 
     * @param integer $year
     * @return array of \Opit\Notes\LeaveBundle\Entity\LeaveDate objects
     */
    public function findAllByYear($year)
    {
        // Set the first day of the year.
        $firstDayOfYear = new \DateTime();
        $firstDayOfYear->setDate($year, 01, 01);
        // Set the last day of the year.
        $lastDayOfYear = new \DateTime();
        $lastDayOfYear->setDate($year, 12, 31);

        // Set the parameters.
        $parameters = array(
            'firstDayOfYear' => $firstDayOfYear->format($year . '-01-01'),
            'lastDayOfYear' => $lastDayOfYear->format($year . '-12-31')
        );

        $leaveDate = $this->createQueryBuilder('ld');

        $leaveDate
            ->where($leaveDate->expr()->gte('ld.holidayDate', ':firstDayOfYear'))
            ->andWhere($leaveDate->expr()->lte('ld.holidayDate', ':lastDayOfYear'))
            ->orderBy('ld.holidayDate', 'ASC')
            ->setParameters($parameters);

        return $leaveDate->getQuery()->getResult();
    }

    /**
     * Get the all avaliable years in the leave dates.
     * 
     * @return array of years
     */
    public function getYears()
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
