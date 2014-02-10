<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\TravelBundle\Entity\Notification;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\UserBundle\Entity\User;

/**
 * TENotification
 * 
 * @ORM\Entity
 */
class TENotification extends Notification
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
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Entity\TravelExpense", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="te_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $travelExpense;

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
     * @return TENotification
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
     * Set travelExpense
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TENotification
     */
    public function setTravelExpense(TravelExpense $travelExpense = null)
    {
        $this->travelExpense = $travelExpense;
    
        return $this;
    }

    /**
     * Get travelExpense
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelExpense 
     */
    public function getTravelExpense()
    {
        return $this->travelExpense;
    }
}