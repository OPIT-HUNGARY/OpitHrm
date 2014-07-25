<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of StatesTravelExpensesRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class StatesTravelExpensesRepository extends EntityRepository
{
    /**
     * Get the current status of a travel expense
     *
     * @param integer $teId travel expense id
     * @return null|Opit\OpitHrm\TravelBundle\Entity\StatesTravelExpenses
     */
    public function getCurrentStatus($teId)
    {
        $travelExpenseState = $this->createQueryBuilder('te')
            ->where('te.travelExpense = :teId')
            ->setParameter(':teId', $teId)
            ->add('orderBy', 'te.id DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $travelExpenseState->getOneOrNullResult();
    }

    /**
     * Find a travel expense's status by status id and travel expense id
     *
     * @param integer $teId
     * @param integer $statusId
     * @param string $order the direction of order
     * @return null|Opit\OpitHrm\TravelBundle\Entity\StatesTravelExpenses
     */
    public function findStatusByStatusId($teId, $statusId, $order = 'DESC')
    {
        $travelExpenseState = $this->createQueryBuilder('te')
            ->where('te.travelExpense = :teId')
            ->andWhere('te.status = :statusId')
            ->orderBy('te.id', $order)
            ->setParameter(':teId', $teId)
            ->setParameter(':statusId', $statusId)
            ->setMaxResults(1)
            ->getQuery();

        return $travelExpenseState->getOneOrNullResult();
    }
}
