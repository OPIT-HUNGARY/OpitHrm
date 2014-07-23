<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\OpitHrm\NotificationBundle\Entity\Notification;

/**
 * LRNotification
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 * 
 * @ORM\Entity
 */
class LRNotification extends Notification
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
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest", inversedBy="notifications")
     * @ORM\JoinColumn(name="lr_id", referencedColumnName="id")
     */
    protected $leaveRequest;
    
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
     * @return TRNotification
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
     * @return TRNotification
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
     * Set leaveRequest
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return LRNotification
     */
    public function setLeaveRequest(\Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest = null)
    {
        $this->leaveRequest = $leaveRequest;

        return $this;
    }

    /**
     * Get leaveRequest
     *
     * @return \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest 
     */
    public function getLeaveRequest()
    {
        return $this->leaveRequest;
    }

    /**
     * Set read
     *
     * @param \Opit\OpitHrm\NotificationBundle\Entity\NotificationStatus $read
     * @return TRNotification
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
     * @return TRNotification
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
