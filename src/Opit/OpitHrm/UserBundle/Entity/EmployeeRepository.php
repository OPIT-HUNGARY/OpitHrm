<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of EmployeeRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage UserBundle
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
        $dq = $this->getTeamEmployeesBaseQuery($id);

        $employees = $dq
            ->orWhere('e.id = :id')
            ->getQuery();

        return $employees->getResult();
    }

    /**
     * Finds team notification recipients for the given user
     *
     * Returns only himself if no teams are assigned
     *
     * @param integer $id
     * @return array
     */
    public function findNotificationRecipients($id)
    {
        $dq = $this->getTeamEmployeesBaseQuery($id);

        $recipients = $dq
            ->andWhere('e.id != :id')
            ->andWhere('e.receiveNotifications = 1')
            ->getQuery();

        return $recipients->getResult();
    }

    /**
     * Builds and returns the base query for team employees
     *
     * @param integer $id
     * @return QueryBuilder
     */
    protected function getTeamEmployeesBaseQuery($id)
    {
        $dq = $this->createQueryBuilder('e0')
            ->select('t0.id')
            ->innerJoin('e0.teams', 't0', 'WITH', 'e0.id = :id');

        $dq2 = $this->createQueryBuilder('e')
            ->leftJoin('e.teams', 't')
            ->where($dq->expr()->in('t.id', $dq->getDQL()))
            ->groupBy('e.id')
            ->setParameter(':id', $id);

        return $dq2;
    }
}
