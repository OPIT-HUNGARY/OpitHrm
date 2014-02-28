<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\TravelBundle\Entity\Notification;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\UserBundle\Entity\User;

/**
 * TRNotification
 *
 * @ORM\Entity
 */
class TRNotification extends Notification
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
     */
    protected $message;
    
    /**
     * @var \DateTime
     */
    protected $dateTime;
    
    /**
     * @var integer
     */
    protected $read;
    
   /**
    * @ORM\ManyToOne(targetEntity="\Opit\Notes\UserBundle\Entity\User",inversedBy="notifications")
    */
    protected $receiver;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Entity\TravelRequest")
     * @ORM\JoinColumn(name="tr_id", referencedColumnName="id")
     */
    protected $travelRequest;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set receiver
     *
     * @param \Opit\Notes\UserBundle\Entity\User $receiver
     * @return TRNotification
     */
    public function setReceiver(User $receiver = null)
    {
        $this->receiver = $receiver;
    
        return $this;
    }

    /**
     * Get receiver
     *
     * @return \Opit\Notes\UserBundle\Entity\User 
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set travelRequest
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @return TRNotification
     */
    public function setTravelRequest(TravelRequest $travelRequest = null)
    {
        $this->travelRequest = $travelRequest;
    
        return $this;
    }

    /**
     * Get travelRequest
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelRequest 
     */
    public function getTravelRequest()
    {
        return $this->travelRequest;
    }
}