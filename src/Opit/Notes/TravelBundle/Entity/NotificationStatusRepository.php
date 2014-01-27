<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of StatesTravelRequestsRepository
 *
 * @author OPIT\kaufmann
 */
class NotificationStatusRepository extends EntityRepository
{
    public function getLastStatus()
    {
        $lastNotificationStatus = $this->createQueryBuilder('ns')
            ->add('orderBy', 'ns.id DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $lastNotificationStatus->getOneOrNullResult();
    }
}
