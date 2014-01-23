<?php

namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\TravelBundle\Entity\Token;
use Opit\Notes\TravelBundle\Entity\StatesTravelExpenses;
use Opit\Notes\TravelBundle\Entity\StatesTravelRequests;

class DefaultController extends Controller
{
    /**
     * Method to change the status of the travel request or travel expense
     *
     * @Route("/changestatus/{travelType}/{status}/{token}", name="OpitNotesTravelBundle_change_status", requirements={ "status" = "\d+" })
     * @Template()
     */
    public function changeStatusAction(Request $request)
    {
        $method = 'get';
        $requestMethod = $request->getMethod();
        $entityManager = $this->getDoctrine()->getManager();
        //get status and Status entity
        $status =$entityManager->getRepository('OpitNotesTravelBundle:Status')
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
        
        if ('POST' === $requestMethod) {
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
}
