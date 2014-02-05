<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Opit\Notes\TravelBundle\Form\TravelType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\TravelBundle\Entity\TRDestination;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManager;

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 */
class TravelController extends Controller
{
    /**
     * @Route("/secured/travel/list", name="OpitNotesTravelBundle_travel_list")
     * @Template()
     */
    public function listAction()
    {
        $securityContext = $this->get('security.context');
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getFilters()->disable('softdeleteable');
        $travelRequests = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->findAll();
        $travelExpenses = $entityManager->getRepository('OpitNotesTravelBundle:TravelExpense');
      
        // te = Travel Expense
        $statusManager = $this->get('opit.manager.status_manager');
        $travelRequestModel = $this->get('opit.model.travel_request');
        $teIds = array();
        $travelRequestStates = array();
        $currentStatusNames = array();
        $isLocked = array();
        
        //if user is not an admin
        if (!$securityContext->isGranted('ROLE_ADMIN')) {
            $allowedTRs = new ArrayCollection();
            //loop through all travel requests
            foreach ($travelRequests as $travelRequest) {
                //if user has the right to view travel request
                if (true === $securityContext->isGranted('VIEW', $travelRequest)) {
                    $currentStatus = $statusManager->getCurrentStatus($travelRequest);
                    
                    $teIds[] = $travelRequestModel->getTravelExpenseId($travelExpenses, $travelRequest);
                    $currentStatusNames[] = $currentStatus->getName();
                    $travelRequestStates[] =
                        $travelRequestModel->getTravelRequestNextStates($travelRequest, $statusManager);
                    $travelRequestAccessRights = $travelRequestModel->setTravelRequestAccessRights(
                        false,
                        $travelRequest->getGeneralManager()->getId(),
                        $this->getUser()->getId(),
                        $currentStatus->getId(),
                        $statusManager->getCurrentStatus($travelRequest)
                    );
                    $isLocked[] = $travelRequestAccessRights;
                    
                    // add travel request to allowed travel requests to show
                    if (false === $travelRequestAccessRights['doNotListTravelRequest']) {
                        $allowedTRs->add($travelRequest);
                    }
                }
            }
        } else {
            foreach ($travelRequests as $travelRequest) {
                $teIds[] = $travelRequestModel->getTravelExpenseId($travelExpenses, $travelRequest);
                $isLocked[] = $travelRequestModel->setTravelRequestAccessRights(true);
                $travelRequestStates[] =
                    $travelRequestModel->getTravelRequestNextStates($travelRequest, $statusManager);
            }
            
            $allowedTRs = $travelRequests;
        }
        
        return array(
            'travelRequests' => $allowedTRs,
            'teIds' => $teIds,
            'travelRequestStates' => $travelRequestStates,
            'isLocked' => $isLocked,
            'currentStatusNames' => $currentStatusNames
        );
    }

    /**
     * @Route("/secured/travel/search", name="OpitNotesTravelBundle_travel_search")
     * @Template()
     */
    public function searchAction()
    {
        $request = $this->getRequest()->request->all();
        $empty = array_filter($request, function ($value) {
            return !empty($value);
        });

        $travelRequests = null;

        if (array_key_exists('resetForm', $request) || empty($empty)) {
             list($travelRequests) = array_values($this->listAction());
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $travelRequests = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')
                                 ->getTravelRequestsBySearchParams($request);
        }
        return $this->render(
            'OpitNotesTravelBundle:Travel:_list.html.twig',
            array("travelRequests" => $travelRequests)
        );
    }

    /**
     * To generate details form for travel requests
     *
     * @Route("/secured/travel/show/details", name="OpitNotesTravelBundle_travel_show_details")
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
        
        return array('travelRequest' => $travelRequest);
    }
    
    /**
     * Method to show and edit travel request
     *
     * @Route("/secured/travel/show/{id}", name="OpitNotesTravelBundle_travel_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Template()
     */
    public function showTravelRequestAction(Request $request)
    {
        $user = $this->getUser();
        $generalManager = null;
        $teamManager = null;
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestId = $request->attributes->get('id');
        $isNewTravelRequest = "new" !== $travelRequestId;
        $travelRequest = ($isNewTravelRequest) ? $this->getTravelRequest($travelRequestId) : new TravelRequest();
        $statusManager = $this->get('opit.manager.status_manager');
        $currentStatus = $statusManager->getCurrentStatus($travelRequest);
        $currentStatusId = $currentStatus->getId();
        $editRights = $this->get('opit.model.travel_request')
            ->setEditRights($user, $travelRequest, $isNewTravelRequest, $currentStatusId);
        
        if (false === $editRights) {
            return $this->redirect($this->generateUrl('OpitNotesTravelBundle_travel_list'));
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
        
        $travelRequestStates[$currentStatusId] = $currentStatus->getName();
        $children = $this->get('opit.model.travel_request')->addChildNodes($travelRequest);
        
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager->getFilters()->disable('softdeleteable');

        $form = $this->handleForm(
            $this->setTravelRequestForm($travelRequest, $entityManager, $isNewTravelRequest),
            $request,
            $isNewTravelRequest,
            $generalManager,
            $teamManager,
            $user->getId(),
            $travelRequest,
            $children
        );
        
        if (true === $form) {
            return $this->redirect($this->generateUrl('OpitNotesTravelBundle_travel_list'));
        }

        $this->isAccessGranted($isNewTravelRequest, $travelRequest);
        
        return array(
            'form' => $form->createView(),
            'travelRequest' => $travelRequest,
            'travelRequestStates' => $travelRequestStates,
            'isEditLocked' => $editRights['isEditLocked'],
            'isStatusLocked' => $editRights['isStatusLocked']
        );
    }
    
    /**
     * @Route("/secured/travel/usersearch", name="OpitNotesTravelBundle_travel_userSearch")
     * @Method({"GET"})
     */
    public function userSearchAction()
    {
        $userNames = array();
        $request = $this->getRequest();
        $term = $request->query->get('term');
        $role = strtoupper('role' . '_' . $request->query->get('role'));
        $users = $this->getDoctrine()->
                        getRepository('OpitNotesUserBundle:User')->
                        findUserByEmployeeNameUsingLike($term);

        foreach ($users as $user) {
            $groups = $user->getGroups();
            foreach ($groups as $group) {
                if ('ALL' === $role || $group->getRole() === $role) {
                    $userNames[] = array(
                        'value'=>$user->getEmployeeName(),
                        'label'=>$user->getEmployeeName(),
                        'id'=>$user->getId()
                    );
                }
            }
        }
        
        return new JsonResponse($userNames);
    }
    
    /**
     * Method to delete one or more travel requests
     *
     * @Route("/secured/travel/delete", name="OpitNotesTravelBundle_travel_delete")
     * @Template()
     * @Method({"POST"})
     */
    public function deleteTravelRequestAction(Request $request)
    {
        $securityContext = $this->get('security.context');
        $ids = $request->request->get('id');
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ($ids as $id) {
            $entityManager = $this->getDoctrine()->getManager();
            $travelRequest = $this->getTravelRequest($id);
            // check if user has sufficient role to delete travel request
            if ($securityContext->isGranted('ROLE_ADMIN') ||
                true === $securityContext->isGranted('DELETE', $travelRequest)) {
                
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
     * @Route("/secured/request/state/", name="OpitNotesTravelBundle_request_state")
     * @Template()
     */
    public function changeTravelRequestStateAction(Request $request)
    {
        $statusId = $request->request->get('statusId');
        $firstStatusId = $request->request->get('firstStatusId');
        $travelRequestId = $request->request->get('travelRequestId');
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequest = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->find($travelRequestId);

        if ($this->get('opit.manager.status_manager')->isNewStatusValid($travelRequest, $firstStatusId)) {
            $statusManager = $this->get('opit.manager.status_manager');
            $statusManager->addStatus($travelRequest, $statusId);
            return new JsonResponse();
        } else {
            return new JsonResponse('error');
        }
    }
    
    /**
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param EntityManager $entityManager
     * @param boolean $isNewTravelRequest
     * @return type
     */
    protected function setTravelRequestForm(TravelRequest $travelRequest, EntityManager $entityManager, $isNewTravelRequest)
    {
        $form = $this->createForm(
            new TravelType($this->get('security.context')->isGranted('ROLE_ADMIN'), $isNewTravelRequest),
            $travelRequest,
            array('em' => $entityManager)
        );
        
        return $form;
    }
    
    /**
     * 
     * @param integer $travelRequestId
     * @return \Opit\Notes\TravelBundle\Entity\TravelRequest
     * @throws CreateNotFoundException
     */
    protected function getTravelRequest($travelRequestId = null)
    {
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();
        
        if (null === $travelRequestId) {
            $travelRequestId = $request->request->get('id');
        }
        
        $travelRequest = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->find($travelRequestId);
        
        if (!$travelRequest) {
            throw $this->createNotFoundException('Missing travel request for id "' . $travelRequestId . '"');
        }
        
        return $travelRequest;
    }
    
    /**
     * 
     * @param boolean $isNewTravelRequest
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @throws AccessDeniedException
     */
    protected function isAccessGranted($isNewTravelRequest, TravelRequest $travelRequest)
    {
        $securityContext = $this->get('security.context');
        if ($isNewTravelRequest) {
            if (true !== $securityContext->isGranted('ROLE_ADMIN') &&
                true !== $securityContext->isGranted('EDIT', $travelRequest)) {
                throw new AccessDeniedException(
                    'Access denied for travel request ' . $travelRequest->getTravelRequestId()
                );
            }
        }
    }
    
    protected function handleForm(
        $form,
        $request,
        $isNewTravelRequest,
        $generalManager,
        $teamManager,
        $userId,
        $travelRequest,
        $children
    ) {
        $oldUser = $travelRequest->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $securityContext = $this->get('security.context');
        $travelRequestModel = $this->get('opit.model.travel_request');
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $isModificationAllowedForUser =
                $travelRequestModel->isModificationAllowedForUser(
                    $isNewTravelRequest,
                    $travelRequest,
                    $userId,
                    $oldUser,
                    $form
                );
            if (true !== $isModificationAllowedForUser) {
                $form = $isModificationAllowedForUser['form'];
                $travelRequest = $isModificationAllowedForUser['travelRequest'];
            }

            if ($form->isValid()) {

                // Persist deleted destinations/accomodations
                $travelRequestModel->removeChildNodes($entityManager, $travelRequest, $children);
                $entityManager->persist($travelRequest);
                $entityManager->flush();

                // Persist travel request object again if travel request id is set (insert actions)
                // set travel request id is handled inside its entity using lifecycle callbacks
                if ($travelRequest->getTravelRequestId()) {
                    $travelRequestModel->addStatus($travelRequest, $entityManager);

                    $entityManager->persist($travelRequest);
                    $entityManager->flush();

                    $travelRequestModel->handleAccessRights(
                        $travelRequest,
                        array(
                            array(
                                'user' => $travelRequest->getGeneralManager(),
                                'mask' => MaskBuilder::MASK_EDIT
                            ),
                            array(
                                'user' => $travelRequest->getTeamManager(),
                                'mask' => MaskBuilder::MASK_EDIT
                            ),
                            array(
                                'user' => $securityContext->getToken()->getUser(),
                                'mask' => MaskBuilder::MASK_OWNER
                            ),
                        ),
                        $generalManager,
                        $teamManager
                    );
                }
                return true;
            }
        }
                
        return $form;
    }
}
