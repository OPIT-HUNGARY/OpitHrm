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
use Opit\Notes\TravelBundle\Entity\Notification;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\UserBundle\Entity\User;

/**
 * TRNotification
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
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
    * @ORM\ManyToOne(targetEntity="\Opit\Notes\UserBundle\Entity\User", inversedBy="notifications")
    */
    protected $receiver;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Entity\TravelRequest", inversedBy="notifications")
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