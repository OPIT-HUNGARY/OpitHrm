<?php

namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\TravelBundle\Entity\Token;
use Opit\Notes\TravelBundle\Entity\StatesTravelExpenses;
use Opit\Notes\TravelBundle\Entity\StatesTravelRequests;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\Notes\TravelBundle\Entity\TRNotification;
use Opit\Notes\TravelBundle\Entity\TENotification;
use Opit\Notes\TravelBundle\Entity\NotificationStatus;

class DefaultController extends Controller
{
    /**
     * Method to change the status of the travel request or travel expense
     *
     * @Route("/changestatus/{gmId}/{travelType}/{status}/{token}", name="OpitNotesTravelBundle_change_status", requirements={ "status" = "\d+", "gmId" = "\d+" })
     * @Template()
     */
    public function changeStatusAction(Request $request)
    {
        $method = 'get';
        $entityManager = $this->getDoctrine()->getManager();
        $generalManager = $entityManager->getRepository('OpitNotesUserBundle:User')
            ->find($request->attributes->get('gmId'));
        //get status and Status entity
        $status = $entityManager->getRepository('OpitNotesTravelBundle:Status')
            ->find($request->attributes->get('status'));
        //get travel type (te=Travel expense, tr=Travel request)
        $travelType = $request->attributes->get('travelType');
        $travelTypeName = ('te' === $travelType) ? 'travel epxense' : 'travel request';
        //get token and Token entity
        $token = $entityManager->getRepository('OpitNotesTravelBundle:Token')
            ->findOneBy(array('token' => $request->attributes->get('token')));
        
        // if $token is not an instance of Token entity throw an exception
        if (false === ($token instanceof Token)) {
            throw $this->createNotFoundException('Security token is not valid. Status cannot be updated.');
        }
        
        if ($request->isMethod('POST')) {
            $method = 'post';
            //get the travel id from the token
            $travelId = $token->getTravelId();

            if ('te' === $travelType) {
                $travel = $entityManager->getRepository('OpitNotesTravelBundle:TravelExpense')->find($travelId);
                $travelStatus = new StatesTravelExpenses();
                $travelStatus->setTravelExpense($travel);
            } elseif ('tr' === $travelType) {
                $travel = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->find($travelId);
                $travelStatus = new StatesTravelRequests();
                $travelStatus->setTravelRequest($travel);
            }
            $travelStatus->setCreatedUser($generalManager);
            $travelStatus->setUpdatedUser($generalManager);
            $travelStatus->setStatus($status);
            $entityManager->persist($travelStatus);
            $entityManager->remove($token);
            $entityManager->flush();
        }
        
        return $this->render(
            'OpitNotesTravelBundle:Shared:updateStatus.html.twig',
            array('status' => strtolower($status->getName()), 'travelTypeName' => $travelTypeName, 'method' => $method)
        );
    }
    
    /**
     * Method to get the history for a travel request and travel expense if it exists
     *
     * @Route("/secured/travel/states/history", name="OpitNotesTravelBundle_travel_states_history")
     * @Template()
     */
    public function getTravelStatusHistoryAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestId = $request->request->get('id');
        $travelRequest = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')
            ->find($travelRequestId);
        $travelExpense = $entityManager->getRepository('OpitNotesTravelBundle:TravelExpense')
            ->findOneBy(array('travelRequest' => $travelRequest));
        $travelRequestStates = $entityManager->getRepository('OpitNotesTravelBundle:StatesTravelRequests')
            ->findBy(array('travelRequest' => $travelRequestId));
        
        $travelExpenseStates = array();
        if (null !== $travelExpense) {
            $travelExpenseStates = $entityManager->getRepository('OpitNotesTravelBundle:StatesTravelExpenses')
                ->findBy(array('travelExpense' => $travelExpense->getId()));
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
        $unreadNotificationCount = count($notificationManager->getUnreadNotifications($currentUser));
        return new JsonResponse($unreadNotificationCount);
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
        $notification = $notificationManager->setNotificationStatus($notification, NotificationStatus::READ);
        $entityManager->persist($notification);
        $entityManager->flush();
        
        return new JsonResponse();
    }
    
    /**
     * Method to delete a notification
     *
     * @Route("/secured/notification/delete/{id}", name="OpitNotesTravelBundle_notification_delete", requirements={ "id" = "\d+" }))
     * @Template()
     * @Method({"GET"})
     */
    public function deleteNotificationAction(Request $request)
    {
        $notificationId = $request->attributes->get('id');
        $notificationManager = $this->get('opit.manager.notification_manager');
        $notificationManager->deleteNotification($notificationId);
        
        return new JsonResponse(array('deleted' => true));
    }
}
