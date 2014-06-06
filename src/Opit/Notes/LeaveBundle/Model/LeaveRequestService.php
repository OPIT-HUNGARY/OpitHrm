<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Model;

use Doctrine\ORM\EntityManager;
use Opit\Notes\LeaveBundle\Manager\LeaveStatusManager;
use Symfony\Component\Security\Core\SecurityContext;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Notes\TravelBundle\Manager\AclManager;
use Opit\Notes\LeaveBundle\Entity\LeaveRequest;
use Opit\Notes\UserBundle\Entity\Employee;
use Opit\Notes\LeaveBundle\Entity\Leave;
use Opit\Notes\LeaveBundle\Entity\LeaveCategory;
use Opit\Component\Utils\Utils;
use Opit\Component\Email\EmailManagerInterface;

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
    protected $mailer;

    /**
     *
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Opit\Notes\LeaveBundle\Manager\LeaveStatusManager $statusManager
     * @param \Opit\Notes\TravelBundle\Manager\AclManager $aclManager
     */
    public function __construct(SecurityContext $securityContext, EntityManager $entityManager, LeaveStatusManager $statusManager, AclManager $aclManager, EmailManagerInterface $mailer)
    {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->statusManager = $statusManager;
        $this->aclManager = $aclManager;
        $this->mailer = $mailer;
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
        $isForApproval = array();

        foreach ($leaveRequests as $leaveRequest) {
            $currentStatus = $this->statusManager->getCurrentStatus($leaveRequest);
            $currentStatusNames[$leaveRequest->getId()] = $currentStatus->getName();

            $isTRLocked = $this->setLeaveRequestAccessRights($leaveRequest, $currentStatus);

            $leaveRequestStates[$leaveRequest->getId()] =
                $this->getNextAvailableStates($leaveRequest);

            $isLocked[$leaveRequest->getId()] = $isTRLocked;

            $isDeleteable[$leaveRequest->getId()] = $this->isLeaveRequestDeleteable($leaveRequest);
            
            $isForApproval[$leaveRequest->getId()] = ($currentStatus->getId() === Status::FOR_APPROVAL);
        }

        return array(
            'leaveRequestStates' => $leaveRequestStates,
            'currentStatusNames' => $currentStatusNames,
            'isLocked' => $isLocked,
            'isDeleteable' => $isDeleteable,
            'isForApproval' => $isForApproval
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
            if ($leaveRequest->getCreatedUser() === $leaveRequest->getGeneralManager()) {
                return false;
            } elseif (Status::APPROVED === $this->statusManager->getCurrentStatus($leaveRequest)->getId()) {
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

    /**
     * Create new instance of leave
     * 
     * @param \Opit\Notes\LeaveBundle\Entity\Leave $leave
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveCategory $leaveCategory
     * @param integer $leaveLength
     * @return \Opit\Notes\LeaveBundle\Entity\Leave
     */
    public function createLeaveInstance(Leave $leave, LeaveRequest $leaveRequest, LeaveCategory $leaveCategory, $leaveLength, $startDate, $endDate)
    {
        $l = new Leave();
        $l->setDescription($leave->getDescription());
        $l->setStartDate($startDate);
        $l->setEndDate($endDate);
        $l->setLeaveRequest($leaveRequest);
        $l->setNumberOfDays($leaveLength);
        $l->setCategory($leaveCategory);
        
        return $l;
    }

    /**
     * Create a new instance of a leave request
     * 
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Opit\Notes\UserBundle\Entity\Employee $employee
     * @return \Opit\Notes\LeaveBundle\Entity\LeaveRequest
     */
    public function createLRInstance(LeaveRequest $leaveRequest, Employee $employee)
    {
        $lr = new LeaveRequest();
        $lr->setEmployee($employee);
        $lr->setGeneralManager($leaveRequest->getGeneralManager());
        $lr->setTeamManager($leaveRequest->getTeamManager());

        return $lr;
    }

    /**
     * Count the number of leave days
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return integer
     */
    public function countLeaveDays($startDate, $endDate)
    {
        $start = $startDate->getTimestamp();
        $end = $endDate->getTimestamp();
        $administrativeLeavesCount = 0;

        $administrativeLeaves = $this->entityManager->getRepository('OpitNotesLeaveBundle:LeaveDate')->getAdminLeavesInDateRange($startDate, $endDate);

        // Count administrative leaves
        foreach ($administrativeLeaves as $date) {
            if ($date['holidayDate']->format('D') != 'Sat' && $date['holidayDate']->format('D') != 'Sun') {
                $administrativeLeavesCount++;
            }
        }

        // Count administrative working days
        $administrativeWorkingDays = $this->entityManager->getRepository('OpitNotesLeaveBundle:LeaveDate')->countLWDBWDateRange($startDate, $endDate, true);
        // Count total days
        $totalDays = $endDate->diff($startDate)->format("%a") + 1;
        // Count total weekend days
        $totalWeekendDays = Utils::countWeekendDays($start, $end);
        // Count total leave days
        $totalLeaveDays = $totalDays - $totalWeekendDays - $administrativeLeavesCount + $administrativeWorkingDays;

        return $totalLeaveDays;
    }
    
    /**
     * Send email about the leave request if it has been created by gm
     * 
     * @param LeaveRequest $leaveRequest
     * @param string $recipient
     * @param array $unpaidLeaveDetails
     * @param string $status Passed if email is sent to employee and not gm
     */
    public function prepareMassLREmail(LeaveRequest $leaveRequest, $recipient, array $unpaidLeaveDetails, $status = null)
    {
        $templateVariables = array();
        $templateVariables['leaveRequest'] = $leaveRequest;
        
        $this->mailer->setRecipient($recipient);
        
        if (null === $status) {
            $this->mailer->setSubject(
                '[NOTES] - System leave requests created'
            );
        } else {
            $templateVariables['statusName'] = $status->getName();
            $templateVariables['isForApproval'] = Status::FOR_APPROVAL === $status->getId() ? true : false;
            
            $this->mailer->setSubject(
                '[NOTES] - System leave request - ' . $status->getName() . ' (' . $leaveRequest->getLeaveRequestId() . ')'
            );
        }
        $this->mailer->setBodyByTemplate('OpitNotesLeaveBundle:Mail:massLeaveRequests.html.twig', array_merge($templateVariables, $unpaidLeaveDetails));
        $this->mailer->sendMail();
    }
}
