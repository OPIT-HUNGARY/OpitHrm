<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Manager;

use Opit\OpitHrm\NotificationBundle\Manager\NotificationManager;
use Opit\OpitHrm\LeaveBundle\Entity\LRNotification;
use Opit\Component\Utils\Utils;
use Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest;
use Doctrine\ORM\EntityManagerInterface;
use Opit\OpitHrm\StatusBundle\Entity\Status;

/**
 * Description of TravelNotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
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
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $resource
     * @param boolean $toGeneralManager
     * @param \Opit\OpitHrm\StatusBundle\Entity\Status $status
     */
    public function addNewLeaveNotification(LeaveRequest $resource, $toGeneralManager, Status $status)
    {
        // get last status name from resource
        $resourceStatus = strtolower($status->getName());
        $message = $resource->getLeaveRequestId();

        if (strpos('approved', $resourceStatus) !== false || strpos('rejected', $resourceStatus) !== false) {
            $message .=  ' has been ' . $resourceStatus . '.';
            $message = ucfirst($message);
        } else {
            $message = 'Status of '  . $message;
            $message .=  ' changed to ' . $resourceStatus . '.';
        }

        $receiver = (false === $toGeneralManager) ?
            $this->entityManager->getRepository('OpitOpitHrmUserBundle:User')->findOneByEmployee($resource->getEmployee()) :
            $resource->getGeneralManager();

        $notification = new LRNotification();
        $notification->setLeaveRequest($resource);
        $notification->setMessage($message);
        $notification->setReceiver($receiver);
        $notification->setDateTime(new \DateTime('now'));
        $this->setNotificationStatus($notification);

        $this->entityManager->persist($notification);

        // Send notifications to additional recipients if status is set to approved
        if ($status->getId() === Status::APPROVED) {
            if ($teamManager = $resource->getTeamManager()) {
                $notificationsTM = clone $notification;
                $notificationsTM->setReceiver($teamManager);
                $this->entityManager->persist($notificationsTM);
            }

            $ccRecipients = $this->entityManager->getRepository('OpitOpitHrmUserBundle:Employee')->findNotificationRecipients($receiver);

            $notifications = array();
            foreach ($ccRecipients as $i => $employee) {
                $notifications[$i] = clone $notification;
                $notifications[$i]->setReceiver($employee->getUser());

                $this->entityManager->persist($notifications[$i]);
            }
        }

        $this->entityManager->flush();

    }
}
