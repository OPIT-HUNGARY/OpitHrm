<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\NotificationBundle\Manager;

use Opit\OpitHrm\NotificationBundle\Entity\NotificationStatus;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Description of NotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage NotificationBundle
 */
abstract class NotificationManager
{
    protected $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Method to get all unread notifications that a user received
     *
     * @param $currentUser
     * @return Notification
     */
    public function getUnreadNotifications($currentUser)
    {
        $unreadNotifications = $this->entityManager->getRepository('OpitOpitHrmNotificationBundle:Notification')
            ->findBy(array('receiver' => $currentUser, 'read' => NotificationStatus::UNREAD));
        
        return $unreadNotifications;
    }
    
    /**
     * Method to get all notifications and set the status of unread notifications to unseen
     * 
     * @param $currentUser
     * @return Notification
     */
    public function getAllNotifications($currentUser)
    {
        // TODO: Unify the 2 queries or refactor the set unseen business logic
        $allNotifications = $this->entityManager
            ->getRepository('OpitOpitHrmNotificationBundle:Notification')
            ->findByReceiver($currentUser);

        // Set new notifications to unseen
        foreach ($allNotifications as $notification) {
            if ($notification->getRead()->getId() === NotificationStatus::UNREAD) {
                $this->setNotificationStatus($notification, NotificationStatus::UNSEEN);
                $this->entityManager->persist($notification);
            }
        }
        
        $this->entityManager->flush();
        
        // Retrieve last 10 matching notifications (see repository method for details)
        $lastNotifications = $this->entityManager->getRepository('OpitOpitHrmNotificationBundle:Notification')
            ->getLastTenNotifications($currentUser->getId());
        
        return $lastNotifications;
    }
    
    /**
     * Method to delete a notification
     * 
     * @param integer $notificationId
     */
    public function deleteNotification($notificationId)
    {
        $notification = $this->entityManager
            ->getRepository('OpitOpitHrmNotificationBundle:Notification')
            ->find($notificationId);
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }
    
    /**
     * Method to set the status of a notification
     * 
     * @param mixed $notification
     * @param integer $statusId
     * @return $notification
     */
    public function setNotificationStatus($notification, $statusId = NotificationStatus::UNREAD)
    {
        $status = $this->entityManager->getRepository('OpitOpitHrmNotificationBundle:NotificationStatus')->find($statusId);
        $notification->setRead($status);

        return $notification;
    }
}
