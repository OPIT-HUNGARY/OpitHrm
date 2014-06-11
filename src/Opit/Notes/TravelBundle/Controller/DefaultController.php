<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\TravelBundle\Entity\Token;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * DefaultController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class DefaultController extends Controller
{
    /**
     * Method to change the status of the travel request or travel expense
     *
     * @Route("/change/travelstatus/{gmId}/{travelType}/{status}/{token}", name="OpitNotesTravelBundle_change_status", requirements={ "status" = "\d+", "gmId" = "\d+" })
     * @Template()
     * @throws CreateNotFoundException
     */
    public function changeStatusAction(Request $request)
    {
        $method = 'get';
        $entityManager = $this->getDoctrine()->getManager();
        //get status and Status entity
        $status = $entityManager->getRepository('OpitNotesStatusBundle:Status')
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
            $this->get('opit.manager.travel_status_manager')->addStatus($travel, $status->getId());
        }

        return $this->render(
            'OpitNotesTravelBundle:Shared:updateStatus.html.twig',
            array('status' => strtolower($status->getName()), 'travelTypeName' => $travelTypeName, 'method' => $method)
        );
    }

    /**
     * Method to get the history for a travel request and travel expense if it exists
     *
     * @Route("/secured/travel/status/history/{id}/{mode}", name="OpitNotesTravelBundle_status_history", requirements={"mode"="tr|te|both", "id"="\d+"}, defaults={"mode"="both"})
     * @Method({"POST"})
     * @Template()
     */
    public function showStatusHistoryAction($id, $mode)
    {
        $travelRequestStates = array();
        $travelExpenseStates = array();
        $elements = array();
        $entityManager = $this->getDoctrine()->getManager();

        $travelRequest = $entityManager
            ->getRepository('OpitNotesTravelBundle:TravelRequest')
            ->find($id);

        if (in_array($mode, array('tr', 'both'))) {
            $travelRequestStates = $entityManager
                ->getRepository('OpitNotesTravelBundle:StatesTravelRequests')
                ->findBy(array('travelRequest' => $travelRequest), array('created' => 'DESC'));

            $elements['tr'] = array(
                'title' => 'Travel Request',
                'collection' => $travelRequestStates,
            );
        }

        if (in_array($mode, array('te', 'both')) && null !== $travelExpense = $travelRequest->getTravelExpense()) {
            $travelExpenseStates = $entityManager
                ->getRepository('OpitNotesTravelBundle:StatesTravelExpenses')
                ->findBy(array('travelExpense' => $travelExpense), array('created' => 'DESC'));

            $elements['te'] = array(
                'title' => 'Travel Expense',
                'collection' => $travelExpenseStates,
            );
        }

        return $this->render(
            'OpitNotesCoreBundle:Shared:statusHistory.html.twig',
            array('elements' => $elements)
        );
    }
}
