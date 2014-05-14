<?php

/*
 * The MIT License
 *
 * Copyright 2014 Marton Kaufmann <kaufmann@opit.hu>.
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

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of TeamRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
class TeamRepository extends EntityRepository
{
    /**
     * Find all employees in teams
     * 
     * @param integer $teamIds
     * @return array
     */
    public function findTeamsEmployees($teamIds)
    {
        $dq = $this->createQueryBuilder('t')
            ->select('e.id, e.employeeName')
            ->where('t.id IN (:ids)')
            ->innerJoin('t.employee', 'e')
            ->setParameter(':ids', $teamIds)
            ->getQuery();
        
        return array_unique($dq->getArrayResult(), SORT_REGULAR);
    }
    
    /**
     * Find all employees in teams
     * 
     * @param integer $teamIds
     * @return array
     */
    public function findTeamsEmployeeIds($teamIds)
    {
        $dq = $this->createQueryBuilder('t')
            ->select('e.id')
            ->where('t.id IN (:ids)')
            ->innerJoin('t.employee', 'e')
            ->setParameter(':ids', $teamIds)
            ->getQuery();
        
        $ids = array();
        foreach ($dq->getArrayResult() as $result) {
            $ids[] = $result['id'];
        }
        
        return array_unique($ids, SORT_REGULAR);
    }
}
