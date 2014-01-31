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
    protected $reciever;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Entity\TravelExpense")
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
     * Set reciever
     *
     * @param \Opit\Notes\UserBundle\Entity\User $reciever
     * @return TENotification
     */
    public function setReciever(User $reciever = null)
    {
        $this->reciever = $reciever;
    
        return $this;
    }

    /**
     * Get reciever
     *
     * @return \Opit\Notes\UserBundle\Entity\User 
     */
    public function getReciever()
    {
        return $this->reciever;
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