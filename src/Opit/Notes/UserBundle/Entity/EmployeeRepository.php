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
class EmployeeRepository extends EntityRepository
{
    /**
     * Find all teams an employee is in
     * 
     * @param integer $employeeId
     * @return array
     */
    public function findEmployeeTeamIds($employeeId)
    {
        $dq = $this->createQueryBuilder('e')
            ->select('t.id')
            ->where('e.id = :id')
            ->innerJoin('e.teams', 't')
            ->setParameter(':id', $employeeId)
            ->getQuery();
        
        $teams = array();
        foreach ($dq->getArrayResult() as $team){
            $teams[] = $team['id'];
        }
        
        return $teams;
    }
    
    public function findAllEmployeeIdNameHydrated()
    {
        return $this->createQueryBuilder('e')->select('e.id, e.employeeName')->getQuery()->getArrayResult();
    }
    
    public function findEmployeeIdNameHydrated($employeeId)
    {
        $dq = $this->createQueryBuilder('e')
            ->select('e.id, e.employeeName')
            ->where('e.id = :id')
            ->setParameter(':id', $employeeId)
            ->getQuery();
        
        return $dq->getArrayResult();
    }
}
