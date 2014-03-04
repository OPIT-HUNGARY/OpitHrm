<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Model;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Dbal\AclProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\TravelBundle\Manager\StatusManager;
use Opit\Notes\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\Notes\TravelBundle\Entity\Status;

/**
 * Description of TravelRequestService
 *
 * @author OPIT\kaufmann
 */
class TravelRequestService
{
    protected $securityContext;
    protected $entityManager;
    protected $aclProvider;
    protected $statusManager;
    
    public function __construct(
        SecurityContext $securityContext,
        EntityManager $entityManager,
        AclProvider $aclProvider,
        StatusManager $statusManager
    ) {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->aclProvider = $aclProvider;
        $this->statusManager = $statusManager;
    }

    /**
     * 
     * @param boolean $isAdmin
     * @param integer $travelRequestGM
     * @param integer $currentUser
     * @param integer $currentStatusId
     * @param \Opit\Notes\TravelBundle\Entity\Status $travelRequestStatus
     * @return type
     */
    public function setTravelRequestAccessRights(
        $isAdmin,
        $travelRequestGM = null,
        $currentUser = null,
        $currentStatusId = null,
        $travelRequestStatus = null
    ) {
        $isTravelExpenseLocked = false;
        $isEditLocked = false;
        $allActionsLocked = false;
        $isAddTravelExpenseLocked = false;
        $doNotListTravelRequest = false;
        $isStatusLocked = false;
        
        if (true === $isAdmin) {
            if (null !== $travelRequestStatus) {
                $isTravelExpenseLocked = true;
            }
        } else {
            if ($travelRequestGM === $currentUser) {
                // travel request cannot be edited
                $isEditLocked = false;
                // travel request cannot be edited or deleted
                $allActionsLocked = false;
                // travel expense cannot be added to travel request
                $isAddTravelExpenseLocked = true;

                if (null !== $travelRequestStatus) {
                    // if the status of the travel request not created do not show the option to view the travel expense
                    if (Status::CREATED === $travelRequestStatus->getId()) {
                        $isTravelExpenseLocked = true;
                    }
                }

                // if travel request has state created do not show it until it has been sent for approval
                if (Status::CREATED === $currentStatusId) {
                    $doNotListTravelRequest = true;
                }
                
                // if travel request has status for approval enable the modification of its status
                if (Status::FOR_APPROVAL !== $currentStatusId) {
                    $isStatusLocked = true;
                    $isEditLocked = true;
                }
            } else {
                // if travel request has been approved allow the option to add a travel expense to it
                if (Status::APPROVED !== $currentStatusId) {
                    $isAddTravelExpenseLocked = true;
                }
                
                // if travel expense has status created or revise allow the modification of it
                if (Status::CREATED !== $currentStatusId && Status::REVISE !== $currentStatusId) {
                    $isEditLocked = true;
                }
                
                // if travel request has been sent for approval lock all action(edit, delete)
                if (Status::FOR_APPROVAL === $currentStatusId) {
                    $allActionsLocked = true;
                }
                
                // if travel request has any of the below statuses disable the option to change its status
                if (Status::APPROVED === $currentStatusId ||
                    Status::REJECTED === $currentStatusId ||
                    Status::FOR_APPROVAL === $currentStatusId) {
                    $isStatusLocked = true;
                }
            }
        }
        
        return array(
            'isEditLocked' => $isEditLocked,
            'allActionsLocked' => $allActionsLocked,
            'isAddTravelExpenseLocked' => $isAddTravelExpenseLocked,
            'doNotListTravelRequest' => $doNotListTravelRequest,
            'isStatusLocked' => $isStatusLocked,
            'isTravelExpenseLocked' => $isTravelExpenseLocked
        );
    }
    
    public function setTravelRequestListingRights($travelRequests, $isAdmin, $user)
    {
        $statusManager = $this->statusManager;
        $currentStatusNames = array();
        $teIds = array();
        $travelRequestStates = array();
        $isLocked = array();
        
        if (!$isAdmin) {
            $allowedTRs = new ArrayCollection();
            //loop through all travel requests
            foreach ($travelRequests as $travelRequest) {
                //if user has the right to view travel request
                if (true === $this->securityContext->isGranted('VIEW', $travelRequest)) {
                    $currentStatus = $statusManager->getCurrentStatus($travelRequest);
                    $travelRequestAccessRights = $this->setTravelRequestAccessRights(
                        false,
                        $travelRequest->getGeneralManager()->getId(),
                        $user->getId(),
                        $currentStatus->getId(),
                        $statusManager->getCurrentStatus($travelRequest)
                    );
                    
                    // add travel request to allowed travel requests to show
                    if (false === $travelRequestAccessRights['doNotListTravelRequest']) {
                        $travelExpense = $travelRequest->getTravelExpense();
                        $teStatus = $statusManager->getCurrentStatus($travelExpense);
                        $teIds[] = array(
                            'id' => ($travelExpense) ? $travelExpense->getId() : 'new',
                            'status' => null !== $teStatus ? $teStatus->getId() : 0
                        );
                        $currentStatusNames[] = $currentStatus->getName();
                        $allowedTRs[] = $travelRequest;
                        $isTRLocked = $travelRequestAccessRights;
                        $travelRequestStates[] =
                            $this->getTravelRequestNextStates($travelRequest, $statusManager);

                        if (Status::PAID === $currentStatus->getId()) {
                            $isTRLocked['isStatusLocked'] = true;
                        }
                        
                        $isLocked[] = $isTRLocked;
                    }
                }
            }
        } else {
            foreach ($travelRequests as $travelRequest) {
                $currentStatus = $statusManager->getCurrentStatus($travelRequest);
                $travelExpense = $travelRequest->getTravelExpense();
                $teStatus = $statusManager->getCurrentStatus($travelExpense);
                $teIds[] = array(
                    'id' => ($travelExpense) ? $travelExpense->getId() : 'new',
                    'status' => null !== $teStatus ? $teStatus->getId() : 0
                );
                $isTRLocked = $this->setTravelRequestAccessRights(true);
                $travelRequestStates[] =
                    $this->getTravelRequestNextStates($travelRequest, $statusManager);
                
                $trStatusCurrent = $this->statusManager->getCurrentStatus($travelRequest)->getId();
                if (Status::APPROVED === $trStatusCurrent || Status::PAID == $trStatusCurrent) {
                    $isTRLocked['isEditLocked'] = true;
                    $isTRLocked['isStatusLocked'] = true;
                }
                $currentStatusNames[] = $currentStatus->getName();
                $isLocked[] = $isTRLocked;
            }
            
            $allowedTRs = $travelRequests;
        }
        
        return array(
            'allowedTRs' => $allowedTRs,
            'teIds' => $teIds,
            'travelRequestStates' => $travelRequestStates,
            'currentStatusNames' => $currentStatusNames,
            'isLocked' => $isLocked
        );
    }
    
    /**
     * Method to check if user is allowed to modify the travel request
     * 
     * @param integer $isNewTravelRequest
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param integer $userId
     * @param \Opit\Notes\UserBundle\Entity\User $oldUser
     * @param type $form
     * @return boolean
     */
    public function isModificationAllowedForUser(
        $isNewTravelRequest,
        TravelRequest $travelRequest,
        $userId,
        User $oldUser,
        $form
    ) {
        // checks if new travel request is being created by a user or by an admin
        if ('new' !== $isNewTravelRequest && !$this->securityContext->isGranted('ROLE_ADMIN')) {
            // if user is owner of travel request
            if (true === $this->securityContext->isGranted('OWNER', $travelRequest)) {
                // if travel request user does not exist or travel request user id does not match current user id
                if (null === $travelRequest->getUser() ||
                    $travelRequest->getUser()->getId() !== $userId) {
                    // reset travel request user
                    $travelRequest->setUser($oldUser);
                    // recreate travel request form
                    $form = $this->setTravelRequestForm($travelRequest, $this->entityManager);
                    // add error to form so it will not validate
                    $form->addError(new FormError('Invalid employee name.'));
                    
                    return array('form' => $form, 'travelRequest' => $travelRequest);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Method to set edit rights for travel request
     * 
     * @param \Opit\Notes\UserBundle\Entity\User $user
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param boolean $isNewTravelRequest
     * @param integer $currentStatusId
     * @return array
     */
    public function setEditRights(User $user, TravelRequest $travelRequest, $isNewTravelRequest, $currentStatusId)
    {
        $isEditLocked = true;
        $isStatusLocked = false;
        $userId = $user->getId();
        if (false === $isNewTravelRequest) {
            // the currently logged in user is always set as default
            $isStatusLocked = true;
            $isEditLocked = false;
        } else {
            if ($userId === $travelRequest->getUser()->getId()) {
                if (Status::CREATED !== $currentStatusId && Status::REVISE !== $currentStatusId) {
                    return false;
                }
                $isEditLocked = false;
            } elseif ($userId === $travelRequest->getGeneralManager()->getId()) {
                if (Status::FOR_APPROVAL !== $currentStatusId) {
                    return false;
                }
            } elseif ($this->securityContext->isGranted('ROLE_ADMIN')) {
                $trCurrentStatus = $this->statusManager->getCurrentStatus($travelRequest)->getId();
                if (Status::APPROVED === $trCurrentStatus || Status::PAID === $trCurrentStatus) {
                    $isEditLocked = true;
                } else {
                    $isEditLocked = false;
                }
            }
        }

        return array('isEditLocked' => $isEditLocked, 'isStatusLocked' => $isStatusLocked);
    }
    
    /**
     * Method to handle add and remove access rights
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param array $users
     * @param string $generalManagerName
     * @param string $teamManagerName
     */
    public function handleAccessRights(TravelRequest $travelRequest, array $users, $gmName, $tmName)
    {
        // try to find acl, used when travel request was modified
        try {
            $acl = $this->aclProvider->findAcl(ObjectIdentity::fromDomainObject($travelRequest));
        // create new acl user when new travel request was created
        } catch (AclNotFoundException $e) {
            $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($travelRequest));
        }
        
        $this->removeAccessRights($acl, $gmName, $tmName);
        
        // loop through users and grant all of them the permission (mask) passed in the array
        if (is_array($users)) {
            foreach ($users as $user) {
                if (null !== $user['user']) {
                    $this->addAccessRights($user['user'], $user['mask'], $acl);
                }
            }
        }
    }
    
    /**
     * Method to add access rights to user for travel request
     * 
     * @param \Opit\Notes\UserBundle\Entity\User $user
     * @param type $mask
     * @param type $acl
     */
    private function addAccessRights($user, $mask, $acl)
    {
        $securityId = UserSecurityIdentity::fromAccount($user);
        $acl->insertObjectAce($securityId, $mask);
        $this->aclProvider->updateAcl($acl);
    }
    
    /**
     * Method to remove user access rights to travel request
     * 
     * @param type $acl
     * @param string $generalManagerName
     * @param string $teamManagerName
     */
    private function removeAccessRights($acl, $generalManagerName, $teamManagerName)
    {
        $aces = $acl->getObjectAces();
        foreach ($aces as $i => $ace) {
            if ($generalManagerName === $ace->getSecurityIdentity()->getUsername() ||
                $teamManagerName === $ace->getSecurityIdentity()->getUsername()) {
                $acl->deleteObjectAce($i);
            }
        }
        $this->aclProvider->updateAcl($acl);
    }
    
    /**
     * Method to add accomodation and destination to travel request
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function addChildNodes(TravelRequest $travelRequest)
    {
        $children = new ArrayCollection();
        
        foreach ($travelRequest->getDestinations() as $destination) {
            $children->add($destination);
        }
        
        foreach ($travelRequest->getAccomodations() as $accomodation) {
            $children->add($accomodation);
        }
        
        return $children;
    }
    
    /**
     * Removes related travel request instances.
     *
     * @param EntityManager $entityManager
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param ArrayCollection $children
     */
    public function removeChildNodes(EntityManager $entityManager, TravelRequest $travelRequest, $children)
    {
        foreach ($children as $child) {
            $getter = ($child instanceof TRDestination) ? 'getDestinations' : 'getAccomodations';
            if (false === $travelRequest->$getter()->contains($child)) {
                $child->setTravelRequest(null);
                $entityManager->remove($child);
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
    public function getTravelRequestNextStates(TravelRequest $travelRequest, $statusManager)
    {
        $currentStatus = $statusManager->getCurrentStatus($travelRequest);
        $currentStatusName = $currentStatus->getName();
        $currentStatusId = $currentStatus->getId();
        
        // handle "paid" status
        $excludeStatusIds = array();
        $relExpenseStatus = $statusManager->getCurrentStatus($travelRequest->getTravelExpense());
        if (!$relExpenseStatus || $relExpenseStatus->getId() != Status::APPROVED) {
            array_push($excludeStatusIds, Status::PAID);
        }
        
        $trSelectableStates = $statusManager->getNextStates($currentStatus, $excludeStatusIds);
        $trSelectableStates[$currentStatusId] = $currentStatusName;
        
        return $trSelectableStates;
    }
    
    /**
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param integer $statusId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeStatus(TravelRequest $travelRequest, $statusId)
    {
        if ($this->statusManager->isValid($travelRequest, $statusId)) {
            $this->statusManager->addStatus($travelRequest, $statusId);
            return new JsonResponse();
        } else {
            return new JsonResponse('error');
        }
    }
}
