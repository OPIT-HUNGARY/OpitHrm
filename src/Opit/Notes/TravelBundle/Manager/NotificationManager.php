<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Manager;

use Opit\Notes\UserBundle\Entity\User;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\TravelBundle\Entity\TENotification;
use Opit\Notes\TravelBundle\Entity\TRNotification;

/**
 * Description of NotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 */
class NotificationManager
{
    protected $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function getUnreadNotifications(User $currentUser)
    {
        $firstReadStatus = current(
            $this->entityManager->getRepository('OpitNotesTravelBundle:NotificationStatus')
            ->findAll()
        );
        $unreadNotifications = $this->entityManager->getRepository('OpitNotesTravelBundle:Notification')
            ->findBy(array('reciever' => $currentUser, 'read' => $firstReadStatus->getId()));
        
        return $unreadNotifications;
    }
    
    public function getAllNotifications(User $currentUser)
    {
        $firstReadStatus = current(
            $this->entityManager->getRepository('OpitNotesTravelBundle:NotificationStatus')
            ->findAll()
        );
        $unreadNotifications = $this->entityManager->getRepository('OpitNotesTravelBundle:Notification')
            ->findBy(array('read' => $firstReadStatus->getId(), 'reciever' => $currentUser->getId()));
        
        foreach ($unreadNotifications as $notification) {
            $notification = $this->changeNotificationReadStatus($notification);
            $this->entityManager->persist($notification);
        }
        
        $this->entityManager->flush();
        
        $allNotifications = $this->entityManager->getRepository('OpitNotesTravelBundle:Notification')
            ->getLastTenNotifications($currentUser->getId());
        
        return $allNotifications;
    }
    
    public function addNewNotification($resource, $toGeneralManager)
    {
        //$notification = new Notification();
        // get last status name from resource
        $resourceStatus = strtolower($resource->getStates()->last()->getStatus()->getName());
        $message = '';
        if ($resource instanceof TravelRequest) {
            $notification = new TRNotification();
            $notification->setTravelRequest($resource);
            $reciever = $resource->getGeneralManager();
            $message .= 'travel request (' . $resource->getTravelRequestId() . ') ';
        } elseif ($resource instanceof TravelExpense) {
            var_dump('add');
            $notification = new TENotification();
            $notification->setTravelExpense($resource);
            $reciever = $resource->getTravelRequest()->getGeneralManager();
            $message .= 'travel expense ';
        }
        if (strpos('approved', $resourceStatus) !== false || strpos('rejected', $resourceStatus) !== false) {
            $message .=  ' has been ' . $resourceStatus . '.';
            $message = ucfirst($message);
        } else {
            $message = 'Status of '  . $message;
            $message .=  ' changed to ' . $resourceStatus . '.';
        }
        
        if (false === $toGeneralManager) {
            $reciever = $resource->getUser();
        }
        var_dump('add2');
        $notification->setMessage($message);
        $notification->setReciever($reciever);
        $notification->setDateTime(new \DateTime('now'));
        $notification = $this->changeNotificationReadStatus($notification);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
    
    public function deleteNotification($notificationId)
    {
        $this->entityManager->getFilters()->enable('softdeleteable');
        $notification = $this->entityManager->getRepository('OpitNotesTravelBundle:Notification')
            ->find($notificationId);
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }
    
    public function setNotificationReadStatus($notification)
    {
        $notification = $this->changeNotificationReadStatus($notification);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
    
    public function changeNotificationReadStatus($notification)
    {
        $lastReadStatus = $this->entityManager->getRepository('OpitNotesTravelBundle:NotificationStatus')
            ->getLastStatus();
        $readStatus = $notification->getRead();
        if ($lastReadStatus->getId() === $readStatus) {
            return $notification;
        } else {
            $readStatus = $readStatus + 1;
            $nextReadStatus = $this->entityManager->getRepository('OpitNotesTravelBundle:NotificationStatus')
                ->find($readStatus);

            return $notification->setRead($nextReadStatus->getId());
        }
    }
}
