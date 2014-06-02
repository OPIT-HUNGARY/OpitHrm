<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of EmployeeRepository
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
        return $this->createQueryBuilder('e')
            ->select('e.id, e.employeeName')
            ->getQuery()
            ->getArrayResult();
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
