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
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\TravelBundle\Entity\TRDestination;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Form\FormError;

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
        $entityManager = $this->getDoctrine()->getManager();
        $securityContext = $this->get('security.context');
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager->getFilters()->disable('softdeleteable');
        $travelRequests = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->findAll();
        $travelExpenses = $entityManager->getRepository('OpitNotesTravelBundle:TravelExpense');
        
        // te = Travel Expense
        $statusManager = $this->get('opit.manager.status_manager');
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
                    $travelExpense = ($travelExpenses->findOneBy(array('travelRequest' => $travelRequest)));
                    if (null !== $travelExpense) {
                        $teIds[] = $travelExpense->getId();
                    } else {
                        $teIds[] = 'new';
                    }

                    $states = $this->getTravelRequestNextStates($travelRequest, $statusManager);
                    $currentStatus = $statusManager->getCurrentStatus($travelRequest);
                    $currentStatusName = $currentStatus->getName();
                    $currentStatusNames[] = $currentStatusName;
                    
                    $travelExpenseStatus = $statusManager->getCurrentStatus($travelExpense);
                    
                    $travelRequestGM = $travelRequest->getGeneralManager()->getId();
                    $currentUser = $this->get('security.context')->getToken()->getUser()->getId();
                    
                    $trAvailability = $this->setTRAvailability(
                        false,
                        $travelRequestGM,
                        $currentUser,
                        $currentStatusName,
                        $travelExpenseStatus
                    );
                    
                    $isLocked[] = $trAvailability;
                    
                    // add travel request to allowed travel requests to show
                    if (false === $trAvailability['doNotListTravelRequest']) {
                        $allowedTRs->add($travelRequest);
                    }
                    
                    $travelRequestStates[] = $states;
                }
            }
        } else {
            foreach ($travelRequests as $travelRequest) {
                $travelExpense = ($travelExpenses->findOneBy(array('travelRequest' => $travelRequest)));
                if (null !== $travelExpense) {
                    $teIds[] = $travelExpense->getId();
                } else {
                    $teIds[] = 'new';
                }
                
                $trAvailability = $this->setTRAvailability(true);
                $isLocked[] = $trAvailability;
                
                $travelRequestStates[] = $this->getTravelRequestNextStates($travelRequest, $statusManager);
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
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestId = $request->attributes->get('id');
        $isNewTravelRequest = "new" !== $travelRequestId;
        $securityContext = $this->get('security.context');
        $currentUser = $securityContext->getToken()->getUser();
        
        $travelRequest = ($isNewTravelRequest) ? $this->getTravelRequest($travelRequestId) : new TravelRequest();
        
        if (false === $isNewTravelRequest) {
            // the currently logged in user is always set as default
            $travelRequest->setUser($currentUser);
        } else {
            $statusManager = $this->get('opit.manager.status_manager');
            $currentStatusName = $statusManager->getCurrentStatus($travelRequest)->getName();
            
            // if travel request has not got the state of created or revise redirect user to listing page
            if ($currentStatusName !== 'Created' && $currentStatusName !== 'Revise') {
                return $this->redirect($this->generateUrl('OpitNotesTravelBundle_travel_list'));
            }
        }
        
        // Track current persisted destination objects
        $children = new ArrayCollection();
        
        foreach ($travelRequest->getDestinations() as $destination) {
            $children->add($destination);
        }
        
        foreach ($travelRequest->getAccomodations() as $accomodation) {
            $children->add($accomodation);
        }
        
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager->getFilters()->disable('softdeleteable');
        
        $form = $this->setTravelRequestForm($travelRequest, $entityManager, $isNewTravelRequest);
        
        $oldUser = $travelRequest->getUser();
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            
            // checks if new travel request is being created by a user or by an admin
            if ('new' !== $isNewTravelRequest && !$securityContext->isGranted('ROLE_ADMIN')) {
                // if user is owner of travel request
                if (true === $securityContext->isGranted('OWNER', $travelRequest)) {
                    // if travel request user does not exist or travel request user id does not match current user id
                    if (null === $travelRequest->getUser() ||
                        $travelRequest->getUser()->getId() !== $currentUser->getId()) {
                        // reset travel request user
                        $travelRequest->setUser($oldUser);
                        // recreate travel request form
                        $form = $this->setTravelRequestForm($travelRequest, $entityManager);
                        // add error to form so it will not validate
                        $form->addError(new FormError('Invalid employee name.'));
                    }
                }
            }
            
            if ($form->isValid()) {
                
                // Persist deleted destinations/accomodations
                $this->removeChildNodes($entityManager, $travelRequest, $children);
                
                $entityManager->persist($travelRequest);
                $entityManager->flush();
                
                // Persist travel request object again if travel request id is set (insert actions)
                // set travel request id is handled inside its entity using lifecycle callbacks
                if ($travelRequest->getTravelRequestId()) {
                    
                    //get status manager
                    $statusManager = $this->get('opit.manager.status_manager');
                    //get current status of travel request
                    $currentStatus = $statusManager->getCurrentStatus($travelRequest);
                    //if travel request current status is null
                    if (null === $currentStatus) {
                        //get the first(default) status and assign in to the newly created travel request
                        $status = $entityManager->getRepository('OpitNotesTravelBundle:Status')->findStatusCreate();
                        //add status to travel request
                        $statusManager->addStatus($travelRequest, $status->getId());
                    }
                    
                    $entityManager->persist($travelRequest);
                    $entityManager->flush();
                    
                    $this->grantAccess(
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
                        )
                    );
                }
                
                return $this->redirect($this->generateUrl('OpitNotesTravelBundle_travel_list'));
            }
        }
        
        // only allow edit of travel request if user has editor or admin rights
        if ($isNewTravelRequest) {
            $securityContext = $securityContext;
            if (true === $securityContext->isGranted('ROLE_ADMIN') ||
                true === $securityContext->isGranted('EDIT', $travelRequest)) {
                return array('form' => $form->createView(), 'travelRequest' => $travelRequest);
            } else {
                throw new AccessDeniedException(
                    'Access denied for travel request ' . $travelRequest->getTravelRequestId()
                );
            }
        }
        
        return array('form' => $form->createView(), 'travelRequest' => $travelRequest);
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
        $user = $request->query->get('user');
        $users = $this->getDoctrine()->
                        getRepository('OpitNotesUserBundle:User')->
                        findUserByEmployeeNameUsingLike($term);

        foreach ($users as $user) {
            $userNames[] = array(
                'value'=>$user->getEmployeeName(),
                'label'=>$user->getEmployeeName(),
                'id'=>$user->getId()
            );
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
            
            // Ensure that no travel requests without permission get deleted
            if ($securityContext->isGranted('ROLE_ADMIN') ||
                true === $securityContext->isGranted('DELETE', $travelRequest)) {
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
        $travelRequestId = $request->request->get('travelRequestId');
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequest = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->find($travelRequestId);
        
        $statusManager = $this->get('opit.manager.status_manager');
        $statusManager->addStatus($travelRequest, $statusId);
        
        return new JsonResponse();
    }
    
    /**
     * Returns a travel request object
     *
     * @param integer $travelRequestId
     * @return mixed  TravelRequest object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
     * Removes related travel request instances.
     *
     * @param object $entityManager
     * @param object $travelRequest
     * @param ArrayCollection $children
     */
    protected function removeChildNodes(&$entityManager, $travelRequest, $children)
    {
        foreach ($children as $child) {
            $getter = ($child instanceof TRDestination) ? 'getDestinations' : 'getAccomodations';
            if (false === $travelRequest->$getter()->contains($child)) {
                $child->setTravelRequest(null);
                $entityManager->remove($child);
            }
        }
    }
    
    protected function grantAccess(TravelRequest $object, $users)
    {
        $aclProvider = $this->container->get('security.acl.provider');
        // try to find acl, used when travel request was modified
        try {
            $acl = $aclProvider->findAcl(ObjectIdentity::fromDomainObject($object));
        // create new acl user when new travel request was created
        } catch (AclNotFoundException $e) {
            $acl = $aclProvider->createAcl(ObjectIdentity::fromDomainObject($object));
        }
        
        // loop through users and grant all of them the permission (mask) passed in the array
        if (is_array($users)) {
            foreach ($users as $user) {
                if (null !== $user['user']) {
                    $this->grantUserAccess($user['user'], $user['mask'], $aclProvider, $acl);
                }
            }
        }
    }
    
    /**
     * Method to get all selectable states for travel request
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param type $statusManager
     * @return array
     */
    protected function getTravelRequestNextStates(TravelRequest $travelRequest, $statusManager)
    {
        $trSelectableStates = array();
        $currentStatus = $statusManager->getCurrentStatus($travelRequest);
        $currentStatusName = $currentStatus->getName();
        $currentStatusId = $currentStatus->getId();
        $trSelectableStates = $statusManager->getNextStates($currentStatus);
        $trSelectableStates[$currentStatusId] = $currentStatusName;
        
        return $trSelectableStates;
    }
    
    /**
     * Method to set which functions will be available on the travel request listing page
     * 
     * @param integer $travelRequestGM
     * @param integer $currentUser
     * @param string $currentStatusName
     * @return boolean $trAvailability
     */
    protected function setTRAvailability($isAdmin, $travelRequestGM = null, $currentUser = null, $currentStatusName = null, $travelExpenseStatus = null)
    {
        $trAvailability = array();
        $trAvailability['isTravelExpenseLocked'] = false;
        if (true === $isAdmin) {
            $trAvailability['isEditLocked'] = false;
            $trAvailability['allActionsLocked'] = false;
            $trAvailability['isAddTravelExpenseLocked'] = false;
            $trAvailability['doNotListTravelRequest'] = false;
            $trAvailability['isStatusLocked'] = false;
            if (null !== $travelExpenseStatus) {
                $travelExpenseStatusName = $travelExpenseStatus->getName();
                    $trAvailability['isTravelExpenseLocked'] = true;
            }
        } else {
            if ($travelRequestGM === $currentUser) {
                // travel request cannot be edited
                $trAvailability['isEditLocked'] = true;
                // travel request cannot be edited or deleted
                $trAvailability['allActionsLocked'] = true;
                // travel expense cannot be added to travel request
                $trAvailability['isAddTravelExpenseLocked'] = true;

                if (null !== $travelExpenseStatus) {
                    $travelExpenseStatusName = $travelExpenseStatus->getName();
                    // if the status of the travel expense created do not show the option to view it
                    if ('Created' === $travelExpenseStatusName) {
                        $trAvailability['isTravelExpenseLocked'] = true;
                    }
                }

                // if travel request has state created do not show it until it has been sent for approval
                if ('Created' === $currentStatusName) {
                    $trAvailability['doNotListTravelRequest'] = true;
                } else {
                    $trAvailability['doNotListTravelRequest'] = false;
                }
                // if travel request has status for approval enable the modification of its status
                if ('For Approval' === $currentStatusName) {
                    $trAvailability['isStatusLocked'] = false;
                } else {
                    $trAvailability['isStatusLocked'] = true;
                }
            } else {
                // user is the owner of the travel request or an admin
                $trAvailability['doNotListTravelRequest'] = false;

                // if travel request has been approved allow the option to add a travel expense to it
                if ('Approved' === $currentStatusName) {
                    $trAvailability['isAddTravelExpenseLocked'] = false;
                } else {
                    $trAvailability['isAddTravelExpenseLocked'] = true;
                }
                // if travel expense has status created or revise allow the modification of it
                if ('Created' === $currentStatusName || 'Revise' === $currentStatusName) {
                    $trAvailability['isEditLocked'] = false;
                } else {
                    $trAvailability['isEditLocked'] = true;
                }
                // if travel request has been sent for approval lock all action(edit, delete)
                if ('For Approval' !== $currentStatusName) {
                    $trAvailability['allActionsLocked'] = false;
                } else {
                    $trAvailability['allActionsLocked'] = true;
                }
                // if travel request has any of the below statuses disable the option to change its status
                if ('Approved' === $currentStatusName ||
                    'Rejected' === $currentStatusName ||
                    'For Approval' === $currentStatusName) {
                    $trAvailability['isStatusLocked'] = true;
                } else {
                    $trAvailability['isStatusLocked'] = false;
                }
            }
        }
        return $trAvailability;
    }
    
    protected function grantUserAccess($user, $mask, $aclProvider, $acl)
    {
        $securityId = UserSecurityIdentity::fromAccount($user);
        $acl->insertObjectAce($securityId, $mask);
        $aclProvider->updateAcl($acl);
    }
    
    protected function setTravelRequestForm(TravelRequest $travelRequest, $entityManager, $isNewTravelRequest)
    {
        $form = $this->createForm(
            new TravelType($this->get('security.context')->isGranted('ROLE_ADMIN'), $isNewTravelRequest),
            $travelRequest,
            array('em' => $entityManager)
        );
        
        return $form;
    }
}
