<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Manager;

use Opit\Notes\TravelBundle\Helper\Utils;
use Opit\Notes\UserBundle\Entity\User;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\TravelBundle\Entity\TENotification;
use Opit\Notes\TravelBundle\Entity\TRNotification;
use Opit\Notes\TravelBundle\Entity\NotificationStatus;

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
        $unreadStatus = $this->entityManager->getRepository('OpitNotesTravelBundle:NotificationStatus')->find(NotificationStatus::UNREAD);
        $unreadNotifications = $this->entityManager->getRepository('OpitNotesTravelBundle:Notification')
            ->findBy(array('receiver' => $currentUser, 'read' => $unreadStatus));
        
        return $unreadNotifications;
    }
    
    public function getAllNotifications(User $currentUser)
    {
        $unreadNotifications = $this->getUnreadNotifications($currentUser);

        foreach ($unreadNotifications as $notification) {
            $this->setNotificationStatus($notification, NotificationStatus::UNSEEN);
            $this->entityManager->persist($notification);
        }
        
        $this->entityManager->flush();
        
        $allNotifications = $this->entityManager->getRepository('OpitNotesTravelBundle:Notification')
            ->getLastTenNotifications($currentUser->getId());
        
        return $allNotifications;
    }
    
    public function addNewNotification($resource, $toGeneralManager)
    {
        $notification = null;
        // get last status name from resource
        $resourceStatus = strtolower($resource->getStates()->last()->getStatus()->getName());
        $message = '';
        if ($resource instanceof TravelRequest) {
            $notification = new TRNotification();
            $reciever = $resource->getGeneralManager();
            $message .= 'travel request (' . $resource->getTravelRequestId() . ') ';
        } elseif ($resource instanceof TravelExpense) {
            var_dump('add');
            $notification = new TENotification();
            $reciever = $resource->getTravelRequest()->getGeneralManager();
            $message .= 'travel expense ';
        }
        call_user_func(array($notification, 'set'.Utils::getClassBasename($resource)), $resource);

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

        $notification->setMessage($message);
        $notification->setReciever($reciever);
        $notification->setDateTime(new \DateTime('now'));
        $notification = $this->setNotificationStatus($notification);
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
    
    public function setNotificationStatus($notification, $status_id = NotificationStatus::UNREAD)
    {
        $status = $this->entityManager->getRepository('OpitNotesTravelBundle:NotificationStatus')->find($status_id);
        $notification->setRead($status);

        return $notification;
    }
}
