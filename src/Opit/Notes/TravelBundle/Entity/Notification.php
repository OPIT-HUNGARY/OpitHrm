<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tokens
 *
 * @ORM\Table(name="notes_notifications")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\NotificationRepository")
 */
class Notification
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
     * @ORM\Column(name="notification_read", type="integer")
     */
    protected $read;
    
    /**
     * 
     * @var integer
     *
     * @ORM\Column(name="travel_id", type="integer")
     */
    protected $travelId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="travel_type", type="string", length=3)
     */
    protected $travelType;
    
   /**
    * @ORM\ManyToOne(targetEntity="\Opit\Notes\UserBundle\Entity\User",inversedBy="notifications")
    * @ORM\JoinColumn(referencedColumnName="id")
    */
    protected $reciever;
    
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
     * @param string $read
     * @return \Opit\Notes\TravelBundle\Entity\Notification
     */
    public function setRead($read)
    {
        $this->read = $read;
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getRead()
    {
        return $this->read;
    }
    
    /**
     * 
     * @param integer $travelId
     * @return \Opit\Notes\TravelBundle\Entity\Notification
     */
    public function setTravelId($travelId)
    {
        $this->travelId = $travelId;
        
        return $this;
    }
    
    /**
     * 
     * @return integer
     */
    public function getTravelId()
    {
        return $this->travelId;
    }
    
    /**
     * 
     * @param string $travelType
     * @return \Opit\Notes\TravelBundle\Entity\Notification
     */
    public function setTravelType($travelType)
    {
        $this->travelType = $travelType;
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getTravelType()
    {
        return $this->travelType;
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
    public function setReciever(User $reciever)
    {
        $this->reciever = $reciever;
        
        return $this;
    }
    
    /**
     * 
     * @return User
     */
    public function getReciever()
    {
        return $this->reciever;
    }
}
