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
    /**
     * Returns the last 10 notifications
     *
     * The following conditions have to be seatisfied:
     * - has to belong to the current user and
     *   - notification date has to be today or
     *   - notification status is unread or unseen
     *
     * @param integer $userId
     * @return object
     */
    public function getLastTenNotifications($userId)
    {
        $qb = $this->createQueryBuilder('n');
        
        $qb->where('n.receiver = :nreceiver')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->gte('n.dateTime', 'CURRENT_DATE()'),
                $qb->expr()->lte('n.read', ':unread')
            ))
            ->setParameter('nreceiver', $userId)
            ->setParameter('unread', NotificationStatus::UNSEEN)
            ->setMaxResults(10)
            ->orderBy('n.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
