<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Opit\OpitHrm\TravelBundle\Form\TravelType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormError;

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelController extends Controller
{
    /**
     * Method to list travel reqeusts
     *
     * @Route("/secured/travel/list", name="OpitOpitHrmTravelBundle_travel_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $showList = $request->request->get('showList');
        $securityContext = $this->get('security.context');
        $config = $this->container->getParameter('pager_config');
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getFilters()->disable('softdeleteable');
        $user = $this->getUser();
        $isSearch = (bool) $request->request->get('issearch');
        $offset = $request->request->get('offset');
        $pagnationParameters = array(
            'firstResult' => ($offset * $config['max_results']),
            'maxResults' => $config['max_results'],
            'currentUser' => $user,
            'isAdmin' => $securityContext->isGranted('ROLE_ADMIN'),
            'isGeneralManager' => $securityContext->isGranted('ROLE_GENERAL_MANAGER'),
        );

        $allRequests = array();
        if ($isSearch) {
            $allRequests = $request->request->all();
        }

        $travelRequests = $entityManager
            ->getRepository('OpitOpitHrmTravelBundle:TravelRequest')
            ->findAllByFiltersPaginated($pagnationParameters, $allRequests);

        $listingRights = $this->get('opit.model.travel_request')
            ->setTravelRequestListingRights($travelRequests);
        $teIds = $listingRights['teIds'];
        $travelRequestStates = $listingRights['travelRequestStates'];
        $currentStatusNames = $listingRights['currentStatusNames'];
        $isLocked = $listingRights['isLocked'];
        $numberOfPages = ceil(count($travelRequests) / $config['max_results']);
        $templateVars = array(
            'travelRequests' => $travelRequests,
            'teIds' => $teIds,
            'travelRequestStates' => $travelRequestStates,
            'isLocked' => $isLocked,
            'currentStatusNames' => $currentStatusNames,
            'numberOfPages' => $numberOfPages,
            'maxPages' => $config['max_pages'],
            'offset' => ($offset + 1),
            'states' => $entityManager->getRepository('OpitOpitHrmStatusBundle:Status')->getStatusNameId()
        );

        if (null === $showList && (null === $offset && !$isSearch)) {
            $template = 'OpitOpitHrmTravelBundle:Travel:list.html.twig';
        } else {
            $template = 'OpitOpitHrmTravelBundle:Travel:_list.html.twig';
        }

        return $this->render($template, $templateVars);
    }

    /**
     * To generate details form for travel requests
     *
     * @Route("/secured/travel/show/details", name="OpitOpitHrmTravelBundle_travel_show_details")
     * @Template()
     */
    public function showDetailsAction()
    {
        $travelRequest = new TravelRequest();
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestPreview = $request->request->get('preview');
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager->getFilters()->disable('softdeleteable');

        // for creating entities for the travel request preview
        if (null !== $travelRequestPreview) {
            $form = $this->createForm(new TravelType(), $travelRequest, array('em' => $entityManager));
            $form->handleRequest($request);
        } else {
            $travelRequest = $this->getTravelRequest();
        }
        // Get travel request costs.
        $estimatedCosts = $this->get('opit.model.travel_request')
            ->getTRCosts($travelRequest);

        $currencyConfig = $this->container->getParameter('currency_config');

        return array(
            'travelRequest' => $travelRequest,
            'estimatedCostsEUR' => $estimatedCosts['EUR'],
            'estimatedCostsHUF' => ceil($estimatedCosts['HUF']),
            'currencyFormat' => $currencyConfig['currency_format']
        );
    }

    /**
     * Method to show and edit travel request
     *
     * @Route("/secured/travel/show/{id}/{fa}", name="OpitOpitHrmTravelBundle_travel_show",
     *   defaults={"id" = "new", "fa" = "new"}, requirements={ "id" = "new|\d+", "fa" = "new|fa" })
     * @Template()
     */
    public function showTravelRequestAction(Request $request)
    {
        $user = $this->getUser();
        $generalManager = null;
        $teamManager = null;
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestId = $request->attributes->get('id');
        $forApproval = $request->attributes->get('fa');
        $isNewTravelRequest = "new" !== $travelRequestId;
        $travelRequest = ($isNewTravelRequest) ? $this->getTravelRequest($travelRequestId) : new TravelRequest();
        $statusManager = $this->get('opit.manager.travel_request_status_manager');
        $currentStatus = $statusManager->getCurrentStatus($travelRequest);
        $currentStatusId = $currentStatus->getId();

        $isEditLocked = false;
        $editRights = $this->get('opit.model.travel_request')
            ->setEditRights($user, $travelRequest, $isNewTravelRequest, $currentStatusId);

        if (false === $editRights && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $isEditLocked = true;
        }

        if (false !== $isNewTravelRequest) {
            $travelRequestStates = $statusManager->getNextStates($currentStatus);
            $generalManager = $travelRequest->getGeneralManager()->getUsername();
            if (null !== $travelRequest->getTeamManager()) {
                $teamManager = $travelRequest->getTeamManager()->getUsername();
            }
        } else {
            $travelRequest->setUser($user);
        }
        // The next available statuses.
        $travelRequestStates[$currentStatusId] = $currentStatus->getName();
        $children = $this->get('opit.model.travel_request')->addChildNodes($travelRequest);
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager->getFilters()->disable('softdeleteable');

        $form = $this->handleForm(
            $this->setTravelRequestForm($travelRequest, $entityManager, $isNewTravelRequest),
            $request,
            $travelRequest,
            $children,
            $forApproval
        );

        if (true === $form) {
            return $this->redirect($this->generateUrl('OpitOpitHrmTravelBundle_travel_list'));
        }

        return array(
            'form' => $form->createView(),
            'travelRequest' => $travelRequest,
            'travelRequestStates' => $travelRequestStates,
            'isEditLocked' => $isEditLocked ? $isEditLocked : $editRights['isEditLocked'],
            'isStatusLocked' => $editRights['isStatusLocked']
        );
    }

    /**
     * Method to delete one or more travel requests
     *
     * @Route("/secured/travel/delete", name="OpitOpitHrmTravelBundle_travel_delete")
     * @Template()
     * @Method({"POST"})
     */
    public function deleteTravelRequestAction(Request $request)
    {
        $securityContext = $this->get('security.context');
        $ids = $request->request->get('deleteMultiple');
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $entityManager = $this->getDoctrine()->getManager();
            $travelRequest = $this->getTravelRequest($id);
            // check if user has sufficient role to delete travel request
            if ($securityContext->isGranted('ROLE_GENERAL_MANAGER') || $travelRequest->getUser()->getId() === $securityContext->getToken()->getUser()->getId()) {

                $travelExpense = $travelRequest->getTravelExpense();

                if (null !== $travelExpense) {
                    $entityManager->remove($travelExpense);
                }
                $entityManager->remove($travelRequest);
            }
        }

        $entityManager->flush();

        return new JsonResponse('0');
    }

    /**
     * Method to change state of travel expense
     *
     * @Route("/secured/request/state/", name="OpitOpitHrmTravelBundle_request_state")
     * @Template()
     */
    public function changeTravelRequestStateAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->get('status');
        $travelRequest = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelRequest')
            ->find($data['foreignId']);

        // Set comment content or null
        $comment = isset($data['comment']) && $data['comment'] ? $data['comment'] : null;
        // Change the travel request's status
        $this->get('opit.model.travel_request')->changeStatus($travelRequest, $data['id'], $comment, false);

        return new JsonResponse();
    }

    /**
     * Set travel request form
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param EntityManager $entityManager
     * @param boolean $isNewTravelRequest
     * @return \Opit\OpitHrm\TravelBundle\Form\TravelType $form
     */
    protected function setTravelRequestForm(TravelRequest $travelRequest, EntityManager $entityManager, $isNewTravelRequest)
    {
        $form = $this->createForm(
            new TravelType($this->get('security.context')->isGranted('ROLE_GENERAL_MANAGER'), $isNewTravelRequest),
            $travelRequest,
            array('em' => $entityManager)
        );

        return $form;
    }

    /**
     * Get the travel request by id
     *
     * @param integer $travelRequestId travel request id
     * @return \Opit\OpitHrm\TravelBundle\Entity\TravelRequest
     * @throws CreateNotFoundException
     */
    protected function getTravelRequest($travelRequestId = null)
    {
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();

        if (null === $travelRequestId) {
            $travelRequestId = $request->request->get('id');
        }

        $travelRequest = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelRequest')->find($travelRequestId);

        if (!$travelRequest) {
            throw $this->createNotFoundException('Missing travel request for id "' . $travelRequestId . '"');
        }

        return $travelRequest;
    }

    protected function handleForm($form, $request, $travelRequest, $children, $forApproval = null)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestService = $this->get('opit.model.travel_request');
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            // Ensure the correct user is processing the travel request
            if (!$travelRequestService->validateTROwner($travelRequest)) {
                $form->addError(new FormError('Invalid employee name.'));
            }

            if ($form->isValid()) {
                $isNew = $travelRequest->getId();
                // Persist deleted destinations/accomodations
                $travelRequestService->removeChildNodes($travelRequest, $children);
                $entityManager->persist($travelRequest);
                $entityManager->flush();

                // Create initial states for new travel request.
                if (null === $isNew) {
                    // Add created status for the new travel request and then send an email.
                    $travelRequestService->changeStatus($travelRequest, Status::CREATED, null, true);
                    // If the TR marked for approval too then modify its status
                    if ('fa' === $forApproval) {
                        $travelRequestService->changeStatus($travelRequest, Status::FOR_APPROVAL);
                    }
                }

                return true;
            }
        }

        return $form;
    }

    /**
     * To send travel leave summary
     *
     * @Route("/secured/travel/employeesummary", name="OpitOpitHrmLeaveBundle_travel_employeesummary")
     * @Template()
     */
    public function employeeTravelInfoBoardAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        //travel request info
        $totalTRCount = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelRequest')
            ->findEmployeeTravelRequest($user->getID());

        $notPendingTRCount = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelRequest')
            ->findEmployeeNotPendingTravelRequest($user->getID());

        $pendingTRCount = $totalTRCount-$notPendingTRCount;

        //travel expense info
        $totalTECount = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelExpense')
            ->findEmployeeTravelExpenseCount($user->getID());

        $notPendingTECount = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelExpense')
            ->findEmployeeNotPendingTravelExpense($user->getID());
        $pendingTECount = $totalTECount - $notPendingTECount;

        return $this->render(
            'OpitOpitHrmTravelBundle:Travel:_employeeTravelInfoBoard.html.twig',
            array('pendingTravelRequestCount' => $pendingTRCount,
                'totalTravelRequestCount' => $totalTRCount,
                'notPendingTravelRequestCount' => $notPendingTRCount,
                'totalTravelExpenseCount' => $totalTECount,
                'pendingTravelExpenseCount' => $pendingTECount,
                'notPendingTravelExpenseCount' => $notPendingTECount
            )
        );
    }
}
