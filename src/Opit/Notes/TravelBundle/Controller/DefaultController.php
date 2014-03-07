<?php

namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\TravelBundle\Entity\Token;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\Notes\TravelBundle\Entity\TRNotification;
use Opit\Notes\TravelBundle\Entity\TENotification;
use Opit\Notes\TravelBundle\Entity\NotificationStatus;
use Opit\Notes\TravelBundle\Helper\Utils;

class DefaultController extends Controller
{
    /**
     * Method to change the status of the travel request or travel expense
     *
     * @Route("/changestatus/{gmId}/{travelType}/{status}/{token}", name="OpitNotesTravelBundle_change_status", requirements={ "status" = "\d+", "gmId" = "\d+" })
     * @Template()
     * @throws CreateNotFoundException
     */
    public function changeStatusAction(Request $request)
    {
        $method = 'get';
        $entityManager = $this->getDoctrine()->getManager();
        //get status and Status entity
        $status = $entityManager->getRepository('OpitNotesTravelBundle:Status')
            ->find($request->attributes->get('status'));
        $travelTypeName = 'te' == $request->attributes->get('travelType') ? 'expense': 'request';
        //get token and Token entity
        $token = $entityManager->getRepository('OpitNotesTravelBundle:Token')
            ->findOneBy(array('token' => $request->attributes->get('token')));
        
        // if $token is not an instance of Token entity throw an exception
        if (false === ($token instanceof Token)) {
            throw $this->createNotFoundException('Security token is not valid. Status cannot be updated.');
        }

        $travel = $entityManager
            ->getRepository('OpitNotesTravelBundle:Travel' . ucfirst($travelTypeName))
            ->find($token->getTravelId());
        if (null === $travel) {
            throw $this->createNotFoundException('Missing travel ' . $travelTypeName . '.');
        }
        
        if ($request->isMethod('POST')) {
            $method = 'post';

            if (null === $travel) {
                throw $this->createNotFoundException('Missing travel ' . $travelTypeName . '.');
            }

            $entityManager->remove($token);
            $entityManager->flush();
            $this->get('opit.manager.status_manager')->addStatus($travel, $status->getId());
        }
        
        return $this->render(
            'OpitNotesTravelBundle:Shared:updateStatus.html.twig',
            array('status' => strtolower($status->getName()), 'travelTypeName' => $travelTypeName, 'method' => $method)
        );
    }
    
    /**
     * Method to get the history for a travel request and travel expense if it exists
     *
     * @Route("/secured/travel/states/history/{mode}", name="OpitNotesTravelBundle_travel_states_history", requirements={"mode"="tr|te|both"}, defaults={"mode"="both"})
     * @Method({"POST"})
     * @Template()
     */
    public function getTravelStatusHistoryAction(Request $request, $mode)
    {
        $travelRequestStates = array();
        $travelExpenseStates = array();
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestId = $request->request->get('id');
        $travelRequest = $entityManager
            ->getRepository('OpitNotesTravelBundle:TravelRequest')
            ->find($travelRequestId);
        
        if (in_array($mode, array('tr', 'both'))) {
            $travelRequestStates = $entityManager
                ->getRepository('OpitNotesTravelBundle:StatesTravelRequests')
                ->findBy(array('travelRequest' => $travelRequest), array('created' => 'DESC'));
        }
        
        if (in_array($mode, array('te', 'both')) && null !== $travelExpense = $travelRequest->getTravelExpense()) {
            $travelExpenseStates = $entityManager
                ->getRepository('OpitNotesTravelBundle:StatesTravelExpenses')
                ->findBy(array('travelExpense' => $travelExpense), array('created' => 'DESC'));
        }
        return $this->render(
            'OpitNotesTravelBundle:Shared:travelStatesHistory.html.twig',
            array(
                'travelRequestStates' => $travelRequestStates,
                'travelExpenseStates' => $travelExpenseStates
            )
        );
    }
    
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
        $notificationManager = $this->get('opit.manager.notification_manager');
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
        $notificationManager = $this->get('opit.manager.notification_manager');
        $notifications = $notificationManager->getAllNotifications($currentUser);
        $travelExpenses = array();
        $travelRequests = array();
        
        foreach ($notifications as $notification) {
            if ($notification instanceof TENotification) {
                $travelExpenses[] = $notification;
            } elseif ($notification instanceof TRNotification) {
                $travelRequests[] = $notification;
            }
        }
        
        return $this->render(
            'OpitNotesTravelBundle:Shared:notifications.html.twig',
            array(
                'travelRequests' => $travelRequests,
                'travelExpenses' => $travelExpenses
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
        $notification = $entityManager->getRepository('OpitNotesTravelBundle:Notification')->find($notificationId);
        $notificationManager = $this->get('opit.manager.notification_manager');
        $notificationManager->setNotificationStatus($notification, NotificationStatus::READ);
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
        $notificationManager = $this->get('opit.manager.notification_manager');
        $notificationManager->deleteNotification($notificationId);
        
        return new JsonResponse(array('deleted' => true));
    }
}
