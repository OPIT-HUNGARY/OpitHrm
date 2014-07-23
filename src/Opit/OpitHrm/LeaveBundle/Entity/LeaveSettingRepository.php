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

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of LeaveSettingRepository
 */
class LeaveSettingRepository extends EntityRepository
{
    /**
     * Get the number of leaves by age
     * 
     * @param integer $age
     * @return 0|string the number of leaves
     */
    public function getNumberOfLeavesByAge($age)
    {
        $leaveSetting = $this->createQueryBuilder('ls');
        
        $leaveSetting->select('MAX(ls.numberOfLeaves)')
            ->leftJoin('ls.leaveGroup', 'lg', 'WITH')
            ->where($leaveSetting->expr()->like('lg.name', ':group'))
            ->andWhere($leaveSetting->expr()->lte('ls.number', ':age'))
            ->setParameter(':age', $age)
            ->setParameter(':group', 'Age');

        $result = $leaveSetting->getQuery()->getSingleScalarResult();
        
        if (null === $result) {
            return 0;
        }
        return $result;
    }
    
    /**
     * Get the number of leaves by children number
     * 
     * @param integer $child
     * @return 0|string the number of leaves
     */
    public function getNumberOfLeavesByChildren($child)
    {
        $leaveSetting = $this->createQueryBuilder('ls');
        
        $leaveSetting->select('MAX(ls.numberOfLeaves)')
            ->leftJoin('ls.leaveGroup', 'lg', 'WITH')
            ->where($leaveSetting->expr()->like('lg.name', ':group'))
            ->andWhere($leaveSetting->expr()->lte('ls.number', ':child'))
            ->setParameter(':child', $child)
            ->setParameter(':group', 'Children');

        $result = $leaveSetting->getQuery()->getSingleScalarResult();
        
        if (null === $result) {
            return 0;
        }
        return $result;
    }
}
