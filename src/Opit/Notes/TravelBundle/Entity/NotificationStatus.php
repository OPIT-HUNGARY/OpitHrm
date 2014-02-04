<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification Status
 *
 * @ORM\Table(name="notes_notification_status")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\NotificationStatusRepository")
 */
class NotificationStatus
{
    const UNREAD = 1;
    const UNSEEN = 2;
    const READ = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="notification_status_name", type="string", length=255)
     */
    protected $notificationStatusName;

    /**
     * 
     * @param integer $id
     * @return \Opit\Notes\TravelBundle\Entity\NotificationStatus
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param string $notificationStatusName
     * @return \Opit\Notes\TravelBundle\Entity\NotificationStatus
     */
    public function setNotificationStatusName($notificationStatusName)
    {
        $this->notificationStatusName = $notificationStatusName;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getNotificationStatusName()
    {
        return $this->notificationStatus;
    }
}