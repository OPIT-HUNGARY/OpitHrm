<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Manager;

use Opit\Notes\TravelBundle\Helper\Utils;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\TravelBundle\Entity\TENotification;
use Opit\Notes\TravelBundle\Entity\TRNotification;

use Opit\Notes\NotificationBundle\Manager\NotificationManager;

/**
 * Description of TravelNotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
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
