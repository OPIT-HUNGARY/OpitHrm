<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Manager;

use Opit\Component\Utils\Utils;
use Opit\OpitHrm\TravelBundle\Entity\TravelExpense;
use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;
use Opit\OpitHrm\TravelBundle\Entity\TENotification;
use Opit\OpitHrm\TravelBundle\Entity\TRNotification;

use Opit\OpitHrm\NotificationBundle\Manager\NotificationManager;

/**
 * Description of TravelNotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelNotificationManager extends NotificationManager
{
    public function __construct($entityManager)
    {
        parent::__construct($entityManager);
    }

    /**
     * Method to add a notification
     * 
     * @param TravelRequest/TravelExpense $resource
     * @param integer $toGeneralManager
     */
    public function addNewTravelNotification($resource, $toGeneralManager, $status)
    {
        // get last status name from resource
        $resourceStatus = strtolower($status->getName());
        $message = '';
        if ($resource instanceof TravelRequest) {
            $notification = new TRNotification();
            $notification->setTravelRequest($resource);
            $receiver = $resource->getGeneralManager();
            $message .= 'travel request (' . $resource->getTravelRequestId() . ') ';
        } elseif ($resource instanceof TravelExpense) {
            $notification = new TENotification();
            $notification->setTravelExpense($resource);
            $receiver = $resource->getTravelRequest()->getGeneralManager();
            $message .= 'travel expense ';
        }
        call_user_func(array($notification, 'set'.Utils::getClassBasename($resource)), $resource);

        if (strpos('approved', $resourceStatus) !== false || strpos('rejected', $resourceStatus) !== false) {
            $message .=  ' has been ' . $resourceStatus . '.';
            $message = ucfirst($message);
        } else {
            $message = 'Status of '  . $message;
            $message .=  'changed to ' . $resourceStatus . '.';
        }
        
        if (false === $toGeneralManager) {
            $receiver = $resource->getUser();
        }
        
        $notification->setMessage($message);
        $notification->setReceiver($receiver);
        $notification->setDateTime(new \DateTime('now'));
        $notification = $this->setNotificationStatus($notification);
        
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
        
    }
}
