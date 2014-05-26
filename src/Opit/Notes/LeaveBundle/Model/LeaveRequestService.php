<?php

/*
 * The MIT License
 *
 * Copyright 2014 Marton Kaufmann <kaufmann@opit.hu>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Opit\Notes\LeaveBundle\Model;

use Doctrine\ORM\EntityManager;
use Opit\Notes\LeaveBundle\Manager\LeaveStatusManager;
use Symfony\Component\Security\Core\SecurityContext;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Notes\TravelBundle\Manager\AclManager;
use Opit\Notes\LeaveBundle\Entity\LeaveRequest;

/**
 * Description of LeaveRequestService
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
class LeaveRequestService
{
    protected $securityContext;
    protected $entityManager;
    protected $statusManager;
    protected $aclManager;
    
    /**
     * 
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Opit\Notes\LeaveBundle\Manager\LeaveStatusManager $statusManager
     * @param \Opit\Notes\TravelBundle\Manager\AclManager $aclManager
     */
    public function __construct(SecurityContext $securityContext, EntityManager $entityManager, LeaveStatusManager $statusManager, AclManager $aclManager)
    {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->statusManager = $statusManager;
        $this->aclManager = $aclManager;
    }
    
    /**
     * Method to set listing rights for the leave requests.
     * 
     * @param object $leaveRequests
     * @return array
     */
    public function setLeaveRequestListingRights($leaveRequests)
    {
        $currentStatusNames = array();
        $leaveRequestStates = array();
        $isLocked = array();
        $isDeleteable = array();
        
        foreach ($leaveRequests as $leaveRequest) {
            $currentStatus = $this->statusManager->getCurrentStatus($leaveRequest);
            $currentStatusNames[$leaveRequest->getId()] = $currentStatus->getName();
            
            $isTRLocked = $this->setLeaveRequestAccessRights($leaveRequest, $currentStatus);
            
            $leaveRequestStates[$leaveRequest->getId()] =
                $this->getNextAvailableStates($leaveRequest);
            
            $isLocked[$leaveRequest->getId()] = $isTRLocked;
            
            $isDeleteable[$leaveRequest->getId()] = $this->isLeaveRequestDeleteable($leaveRequest);
        }
        
        return array(
            'leaveRequestStates' => $leaveRequestStates,
            'currentStatusNames' => $currentStatusNames,
            'isLocked' => $isLocked,
            'isDeleteable' => $isDeleteable
        );
    }
    
    /**
     * Method to check if leave request is deleteable by the user.
     * 
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return boolean
     */
    public function isLeaveRequestDeleteable(LeaveRequest $leaveRequest)
    {
        if (!$this->securityContext->isGranted('ROLE_GENERAL_MANAGER') && !$this->securityContext->isGranted('ROLE_ADMIN')) {
            if (Status::APPROVED === $this->statusManager->getCurrentStatus($leaveRequest)->getId()) {
                foreach ($leaveRequest->getLeaves() as $leave) {
                    if ($leave->getStartDate() < new \DateTime()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    /**
     * 
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Opit\Notes\StatusBundle\Entity\Status $currentStatus
     * @return array
     */
    public function setLeaveRequestAccessRights(LeaveRequest $leaveRequest, Status $currentStatus)
    {
        $isEditLocked = true;// leave request can not be edited
        $isStatusLocked = true;// status can not be changed
        $unlockedStates = array();
        $currentEmployee = $this->securityContext->getToken()->getUser()->getEmployee();
        $isGeneralManager = $this->isUserGeneralManager($leaveRequest);

        if ($leaveRequest->getEmployee()->getId() === $currentEmployee->getId()) {
            if (in_array($currentStatus->getId(), array(Status::CREATED, Status::REVISE))) {
                $isEditLocked = false;
            }
            
            if ($isGeneralManager) {
                $unlockedStates = array(Status::FOR_APPROVAL);
            }
            
            if (in_array($currentStatus->getId(), array_merge(array(Status::CREATED, Status::REVISE), $unlockedStates))) {
                $isStatusLocked = false;
            }
        } elseif ($isGeneralManager) {
            if (Status::FOR_APPROVAL === $currentStatus->getId()) {
                $isStatusLocked = false;
            }
        }

        return array(
            'isEditLocked' => $isEditLocked,
            'isStatusLocked' => $isStatusLocked
        );
    }
    
    /**
     * 
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return type
     */
    public function isUserGeneralManager(LeaveRequest $leaveRequest)
    {
        return $leaveRequest->getGeneralManager()->getEmployee()->getId() === $this->securityContext->getToken()->getUser()->getEmployee()->getId();
    }
    
    /**
     * 
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return array
     */
    public function getNextAvailableStates(LeaveRequest $leaveRequest)
    {
        $currentStatus = $this->statusManager->getCurrentStatus($leaveRequest);
        $currentStatusName = $currentStatus->getName();
        $currentStatusId = $currentStatus->getId();
        
        $lrSelectableStates = $this->statusManager->getNextStates($currentStatus, array());
        $lrSelectableStates[$currentStatusId] = $currentStatusName;
        
        return $lrSelectableStates;
    }
}
