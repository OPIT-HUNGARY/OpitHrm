<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Manager;

use Opit\Notes\NotificationBundle\Manager\NotificationManager;
use Opit\Notes\LeaveBundle\Entity\LRNotification;
use Opit\Component\Utils\Utils;
use Opit\Notes\LeaveBundle\Entity\LeaveRequest;
use Doctrine\ORM\EntityManagerInterface;
use Opit\Notes\StatusBundle\Entity\Status;

/**
 * Description of TravelNotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class LeaveNotificationManager extends NotificationManager
{
    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }



    /**
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $resource
     * @param boolean $toGeneralManager
     * @param \Opit\Notes\StatusBundle\Entity\Status $status
     */
    public function addNewLeaveNotification(LeaveRequest $resource, $toGeneralManager, Status $status)
    {
        // get last status name from resource
        $resourceStatus = strtolower($status->getName());
        $message = '';
        $notification = new LRNotification();
        $notification->setLeaveRequest($resource);
        $receiver = $resource->getGeneralManager();
        $message .= 'leave request ';

        call_user_func(array($notification, 'set'.Utils::getClassBasename($resource)), $resource);

        if (strpos('approved', $resourceStatus) !== false || strpos('rejected', $resourceStatus) !== false) {
            $message .=  ' has been ' . $resourceStatus . '.';
            $message = ucfirst($message);
        } else {
            $message = 'Status of '  . $message;
            $message .=  'changed to ' . $resourceStatus . '.';
        }

        if (false === $toGeneralManager) {
            $receiver = $this->entityManager
            ->getRepository('OpitNotesUserBundle:User')->findOneByEmployee($resource->getEmployee());
        }

        $notification->setMessage($message);
        $notification->setReceiver($receiver);
        $notification->setDateTime(new \DateTime('now'));
        $notification = $this->setNotificationStatus($notification);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

    }
}
