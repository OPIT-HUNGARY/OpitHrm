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

/**
 * Notification Status
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="notes_notification_status")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\NotificationStatusRepository")
 */
class NotificationStatus
{
    const UNREAD = 1;
    const UNSEEN = 2;
    const READ = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
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
     * @param integer $id
     * @return \Opit\Notes\TravelBundle\Entity\NotificationStatus
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
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