<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of StatesTravelExpensesRepository
 *
 * @author OPIT\kaufmann
 */
class StatesTravelExpensesRepository extends EntityRepository
{
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
}
