<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\OpitHrm\NotificationBundle\Entity\Notification;

/**
 * TENotification
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 * 
 * @ORM\Entity
 */
class TENotification extends Notification
{
    /**
     * @var integer
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
     * @var \Opit\OpitHrm\NotificationBundle\Entity\NotificationStatus
     */
    protected $read;

    /**
     * @var \Opit\OpitHrm\UserBundle\Entity\User
     */
    protected $receiver;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Entity\TravelExpense", inversedBy="notifications")
     * @ORM\JoinColumn(name="te_id", referencedColumnName="id")
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
     * Set message
     *
     * @param string $message
     * @return TENotification
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return TENotification
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * Get dateTime
     *
     * @return \DateTime 
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Set travelExpense
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TENotification
     */
    public function setTravelExpense(\Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense = null)
    {
        $this->travelExpense = $travelExpense;

        return $this;
    }

    /**
     * Get travelExpense
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\TravelExpense 
     */
    public function getTravelExpense()
    {
        return $this->travelExpense;
    }

    /**
     * Set read
     *
     * @param \Opit\OpitHrm\NotificationBundle\Entity\NotificationStatus $read
     * @return TENotification
     */
    public function setRead(\Opit\OpitHrm\NotificationBundle\Entity\NotificationStatus $read = null)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Get read
     *
     * @return \Opit\OpitHrm\NotificationBundle\Entity\NotificationStatus 
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Set receiver
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\User $receiver
     * @return TENotification
     */
    public function setReceiver(\Opit\OpitHrm\UserBundle\Entity\User $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \Opit\OpitHrm\UserBundle\Entity\User 
     */
    public function getReceiver()
    {
        return $this->receiver;
    }
}
