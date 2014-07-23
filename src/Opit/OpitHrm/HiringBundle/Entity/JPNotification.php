<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\OpitHrm\NotificationBundle\Entity\Notification;

/**
 * LRNotification
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 * 
 * @ORM\Entity
 */
class JPNotification extends Notification
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
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\HiringBundle\Entity\JobPosition", inversedBy="notifications")
     * @ORM\JoinColumn(name="jp_id", referencedColumnName="id")
     */
    protected $jobPosition;  
    
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
     * Set jobPosition
     *
     * @param \Opit\OpitHrm\HiringBundle\Entity\JobPosition $jobPosition
     * @return JPNotification
     */
    public function setJobPosition(\Opit\OpitHrm\HiringBundle\Entity\JobPosition $jobPosition = null)
    {
        $this->jobPosition = $jobPosition;

        return $this;
    }

    /**
     * Get jobPosition
     *
     * @return \Opit\OpitHrm\HiringBundle\Entity\JobPosition 
     */
    public function getJobPosition()
    {
        return $this->jobPosition;
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
