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
class NotificationRepository extends EntityRepository
{
    public function getLastTenNotifications($userId)
    {
        $lastTenNotifications = $this->createQueryBuilder('n')
            ->add('orderBy', 'n.id DESC')
            ->where('n.reciever = :nreciever')
            ->setParameter(':nreciever', $userId)
            ->setMaxResults(10)
            ->getQuery();

        return $lastTenNotifications->getResult();
    }
}
