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

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
        $dq = $this->createQueryBuilder('l')->join('l.leaves', 'r');
        
        if (isset($parameters['startDate']) && $parameters['startDate'] !== '') {
            $dq->andWhere('r.startDate > :startDate');
            $dq->setParameter(':startDate', $parameters['startDate']);
        }
        
        if (isset($parameters['endDate']) && $parameters['endDate'] !== '') {
            $dq->andWhere('r.endDate < :endDate');
            $dq->setParameter(':endDate', $parameters['endDate']);
        }
        
        if (isset($parameters['leaveId']) && $parameters['leaveId'] !== '') {
            $dq->andWhere('l.leaveRequestId LIKE :leaveId');
            $dq->setParameter(':leaveId', '%'.$parameters['leaveId'].'%');
        }

        if (!$pagnationParameters['isAdmin']) {
            $dq->andWhere($dq->expr()->eq('l.employee', ':employee'));
            $dq->setParameter(':employee', $pagnationParameters['employee']);
        }
 
        $dq->setFirstResult($pagnationParameters['firstResult']);
        $dq->setMaxResults($pagnationParameters['maxResults']);
                
        return new Paginator($dq->getQuery(), true);
    }
}
