<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tokens
 *
 * @ORM\Table(name="notes_notification_status")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\NotificationStatusRepository")
 */
class NotificationStatus
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
