<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\Notes\TravelBundle\Entity\TRNotification;
use Opit\Notes\TravelBundle\Entity\TENotification;
use Opit\Notes\LeaveBundle\Entity\LRNotification;
use Opit\Notes\HiringBundle\Entity\JPNotification;
use Opit\Notes\NotificationBundle\Entity\NotificationStatus;
use Opit\Notes\HiringBundle\Entity\ApplicantNotification;

/**
 * NotificationController
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage NotificationBundle
 */
class NotificationController extends Controller
{
    /**
     * Method to get number of unread notifications
     *
     * @Route("/secured/notifications/unread", name="OpitNotesTravelBundle_notifications_unread_count")
     * @Template()
     * @Method({"POST"})
     */
    public function getUnreadNotificationsCountAction()
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();
        $notificationManager = $this->get('opit.manager.base_notification_manager');
        $unreadCount = count($notificationManager->getUnreadNotifications($currentUser));
        return new JsonResponse($unreadCount);
    }
    
    /**
     * Method to get all notifications
     *
     * @Route("/secured/notifications/all", name="OpitNotesTravelBundle_notifications_all")
     * @Template()
     * @Method({"POST"})
     */
    public function getAllNotificationsAction()
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();
        $notificationManager = $this->get('opit.manager.base_notification_manager');
        $notifications = $notificationManager->getAllNotifications($currentUser);
        $travelExpenses = array();
        $travelRequests = array();
        $leaveRequests = array();
        $jobPositions = array();
        $applicants = array();
        
        foreach ($notifications as $notification) {
            if ($notification instanceof TENotification) {
                $travelExpenses[] = $notification;
            } elseif ($notification instanceof TRNotification) {
                $travelRequests[] = $notification;
            } elseif ($notification instanceof LRNotification) {
                $leaveRequests[] = $notification;
            } elseif ($notification instanceof JPNotification) {
                $jobPositions[] = $notification;
            } elseif ($notification instanceof ApplicantNotification) {
                $applicants[] = $notification;
            }
        }
        
        return $this->render(
            'OpitNotesNotificationBundle:Notification:notifications.html.twig',
            array(
                'travelRequests' => $travelRequests,
                'travelExpenses' => $travelExpenses,
                'leaveRequests' => $leaveRequests,
                'jobPositions' => $jobPositions,
                'applicants' => $applicants,
            )
        );
    }
    
    /**
     * Method to change the read state of one notification
     *
     * @Route("/secured/notifications/state/change", name="OpitNotesTravelBundle_notifications_state_change")
     * @Template()
     * @Method({"POST"})
     */
    public function changeNotificationStateAction(Request $request)
    {
        $notificationId = $request->request->get('id');
        $entityManager = $this->getDoctrine()->getManager();
        $notification = $entityManager->getRepository('OpitNotesNotificationBundle:Notification')->find($notificationId);
        $notificationManager = $this->get('opit.manager.base_notification_manager');
        $notification = $notificationManager->setNotificationStatus($notification, NotificationStatus::READ);
        $entityManager->persist($notification);
        $entityManager->flush();

        return new JsonResponse(array('success' => ($notification->getRead()->getId() === NotificationStatus::READ)));
    }
    
    /**
     * Method to delete a notification
     *
     * @Route("/secured/notification/delete", name="OpitNotesTravelBundle_notification_delete")
     * @Template()
     * @Method({"POST"})
     */
    public function deleteNotificationAction(Request $request)
    {
        $notificationId = $request->request->get('id');
        $notificationManager = $this->get('opit.manager.base_notification_manager');
        $notificationManager->deleteNotification($notificationId);
        
        return new JsonResponse(array('deleted' => true));
    }
}
