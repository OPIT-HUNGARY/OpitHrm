<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\TravelBundle\Manager\TravelStatusManager;
use Opit\Notes\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Notes\TravelBundle\Model\TravelResourceInterface;
use Opit\Notes\TravelBundle\Manager\AclManager;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Opit\Notes\TravelBundle\Manager\TravelNotificationManager;

/**
 * Description of TravelRequestService
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TravelRequestService
{
    protected $securityContext;
    protected $entityManager;
    protected $statusManager;
    protected $aclManager;
    protected $travelNotification;
    
    public function __construct(
        SecurityContext $securityContext,
        EntityManagerInterface $entityManager,
        TravelStatusManager $statusManager,
        AclManager $aclManager,
        TravelNotificationManager $travelNotificationManager
    ) {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->statusManager = $statusManager;
        $this->aclManager = $aclManager;
        $this->travelNotificationManager = $travelNotificationManager;
    }
    
    public function isUserGeneralManager(TravelResourceInterface $travelRequest)
    {
        return $travelRequest->getGeneralManager()->getId() === $this->securityContext->getToken()->getUser()->getId();
    }


    /**
     * 
     * @param Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param integer Opit\Notes\StatusBundle\Entity\Status $currentStatus
     * @return array
     */
    public function setTravelRequestAccessRights(TravelResourceInterface $travelRequest, $currentStatus)
    {
        $isEditLocked = true;// travel request can not be edited
        $isTEAddLocked = true;// travel expense can not be added
        $isStatusLocked = true;// status can not be changed
        $unlockedStates = array();
        $currentUser = $this->securityContext->getToken()->getUser();

        if ($travelRequest->getUser()->getId() === $currentUser->getId()) {
            // Show add travel expense in case travel expense is approved.
            if (Status::APPROVED === $currentStatus->getId()) {
                $isTEAddLocked = false;
            }

            if (in_array($currentStatus->getId(), array(Status::CREATED, Status::REVISE))) {
                $isEditLocked = false;
            }
            
            if ($this->isUserGeneralManager($travelRequest)) {
                $unlockedStates = array(Status::FOR_APPROVAL);
            }
            
            if (in_array($currentStatus->getId(), array_merge(array(Status::CREATED, Status::REVISE), $unlockedStates))) {
                $isStatusLocked = false;
            }
        } elseif ($this->isUserGeneralManager($travelRequest)) {
            if (Status::FOR_APPROVAL === $currentStatus->getId()) {
                $isStatusLocked = false;
            }
        }

        // Unlock edit mode for admins at all times
        if ($this->securityContext->isGranted('ROLE_ADMIN')) {
            $isEditLocked = false;// travel request can be edited
        }
        
        return array(
            'isTREditLocked' => $isEditLocked,
            'isAddTravelExpenseLocked' => $isTEAddLocked,
            'isStatusLocked' => $isStatusLocked
        );
    }
    
    public function setTravelRequestListingRights($travelRequests)
    {
        $currentStatusNames = array();
        $teIds = array();
        $travelRequestStates = array();
        $isLocked = array();
        
        //loop through all travel requests
        foreach ($travelRequests as $travelRequest) {
            $currentStatus = $this->statusManager->getCurrentStatus($travelRequest);

            // add travel request to allowed travel requests to show
            $travelExpense = $travelRequest->getTravelExpense();
            $teStatus = $this->statusManager->getCurrentStatus($travelExpense);
            $teIds[$travelRequest->getId()] = array(
                'id' => ($travelExpense) ? $travelExpense->getId() : 'new',
                'status' => null !== $teStatus ? $teStatus->getId() : 0,
                'statusName' => null !== $teStatus ? $teStatus->getName() : '',
            );
            $currentStatusNames[$travelRequest->getId()] = $currentStatus->getName();
            $isTRLocked = $this->setTravelRequestAccessRights($travelRequest, $currentStatus);
            $travelRequestStates[$travelRequest->getId()] =
                $this->getNextAvailableStates($travelRequest);

            if (!$this->securityContext->isGranted('ROLE_ADMIN') && Status::PAID === $currentStatus->getId()) {
                $isTRLocked['isStatusLocked'] = true;
            }

            $isLocked[$travelRequest->getId()] = $isTRLocked;
        }
        
        return array(
            'teIds' => $teIds,
            'travelRequestStates' => $travelRequestStates,
            'currentStatusNames' => $currentStatusNames,
            'isLocked' => $isLocked
        );
    }
    
    /**
     * Method to check if user is allowed to modify the travel request
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @return boolean
     */
    public function validateTROwner(TravelRequest $travelRequest)
    {
        $valid = false;
        
        // checks if travel request is being modified by an admin
        if ($this->securityContext->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // if travel request user is the current user, pass the validation
        if ($travelRequest->getUser()->getId() === $this->securityContext->getToken()->getUser()->getId()) {
            $valid = true;
        }
        
        return $valid;
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
            $getter = ($child instanceof \Opit\Notes\TravelBundle\Entity\TRDestination) ? 'getDestinations' : 'getAccomodations';
            if (false === $travelRequest->$getter()->contains($child)) {
                $child->setTravelRequest();
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
    public function getNextAvailableStates(TravelRequest $travelRequest)
    {
        $currentStatus = $this->statusManager->getCurrentStatus($travelRequest);
        $currentStatusName = $currentStatus->getName();
        $currentStatusId = $currentStatus->getId();
        
        // handle "paid" status
        $excludeStatusIds = array();
        $relExpenseStatus = $this->statusManager->getCurrentStatus($travelRequest->getTravelExpense());
        if (!$relExpenseStatus || $relExpenseStatus->getId() != Status::APPROVED) {
            array_push($excludeStatusIds, Status::PAID);
        }
        
        $trSelectableStates = $this->statusManager->getNextStates($currentStatus, $excludeStatusIds);
        $trSelectableStates[$currentStatusId] = $currentStatusName;
        
        return $trSelectableStates;
    }
    
    /**
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param integer $statusId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeStatus(TravelRequest $travelRequest, $statusId, $validationDisabled = false)
    {
        if ($validationDisabled || $this->statusManager->isValid($travelRequest, $statusId)) {
            // Manage travel request access control
            switch ($statusId) {
                case Status::CREATED:
                    // Grant owner access
                    $this->aclManager->grant($travelRequest, $this->securityContext->getToken()->getUser());
                    break;
                case Status::FOR_APPROVAL:
                    // Grant view access for managers
                    $this->aclManager->grant($travelRequest, $travelRequest->getGeneralManager(), MaskBuilder::MASK_VIEW);
                    if ($travelRequest->getTeamManager()) {
                        $this->aclManager->grant($travelRequest, $travelRequest->getTeamManager(), MaskBuilder::MASK_VIEW);
                    }
                    break;
            }
            
            $status = $this->statusManager->addStatus($travelRequest, $statusId);
            
            // send a new notification when travel request or expense status changes
            $this->travelNotificationManager->addNewTravelNotification($travelRequest, (Status::FOR_APPROVAL === $status->getId() ? true : false), $status);
            
            return new JsonResponse();
        } else {
            return new JsonResponse('error');
        }
    }
}
