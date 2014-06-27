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
     * Finds employees in the same teams for the given user
     *
     * Returns only himself if no teams are assigned
     *
     * @param integer $id
     * @return array
     */
    public function findTeamEmployees($id)
    {
       $dq = $this->createQueryBuilder('e0')
            ->select('t0.id')
            ->innerJoin('e0.teams', 't0', 'WITH', 'e0.id = :id');

        $dq2 = $this->createQueryBuilder('e')
            ->leftJoin('e.teams', 't');
        $employees = $dq2
            ->where($dq2->expr()->in('t.id', $dq->getDQL()))
            ->orWhere('e.id = :id')
            ->groupBy('e.id')
            ->setParameter(':id', $id)
            ->getQuery();

        return $employees->getResult();
    }
}
