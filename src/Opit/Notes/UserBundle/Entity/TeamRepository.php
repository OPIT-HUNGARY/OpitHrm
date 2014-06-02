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
