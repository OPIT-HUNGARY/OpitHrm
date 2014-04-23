<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\UserBundle\Entity\User;
use Opit\Notes\TravelBundle\Entity\NotificationStatus;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tokens
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 * 
 * @ORM\Table(name="notes_notifications")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\NotificationRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"te" = "TENotification", "tr" = "TRNotification"})
 */
abstract class Notification
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
     * @ORM\Column(name="message", type="string", length=255)
     */
    protected $message;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_time", type="datetime")
     * @Assert\DateTime()
     */
    protected $dateTime;
    
    /**
     * 
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Opit\Notes\TravelBundle\Entity\NotificationStatus")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $read;
    
   /**
    * @ORM\ManyToOne(targetEntity="\Opit\Notes\UserBundle\Entity\User",inversedBy="notifications")
    * @ORM\JoinColumn(referencedColumnName="id")
    */
    protected $receiver;
    
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
     * @param string $message
     * @return \Opit\Notes\TravelBundle\Entity\Notification
     */
    public function setMessage($message)
    {
        $this->message = $message;
       
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * 
     * @param string $notificationDateTime
     * @return \Opit\Notes\TravelBundle\Entity\Notification
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
    
    /**
     * 
     * @param \Opit\Notes\UserBundle\Entity\User $generalManager
     * @return \Opit\Notes\TravelBundle\Entity\Notification
     */
    public function setReceiver(User $receiver)
    {
        $this->receiver = $receiver;
        
        return $this;
    }
    
    /**
     * 
     * @return User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set read
     *
     * @param \Opit\Notes\TravelBundle\Entity\NotificationStatus $read
     * @return Notification
     */
    public function setRead(NotificationStatus $read = null)
    {
        $this->read = $read;
    
        return $this;
    }

    /**
     * Get read
     *
     * @return \Opit\Notes\TravelBundle\Entity\NotificationStatus
     */
    public function getRead()
    {
        return $this->read;
    }
}
