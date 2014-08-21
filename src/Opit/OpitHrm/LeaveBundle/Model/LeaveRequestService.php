<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Model;

use Doctrine\ORM\EntityManager;
use Opit\OpitHrm\LeaveBundle\Manager\LeaveStatusManager;
use Symfony\Component\Security\Core\SecurityContext;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest;
use Opit\OpitHrm\UserBundle\Entity\Employee;
use Opit\OpitHrm\LeaveBundle\Entity\Leave;
use Opit\OpitHrm\LeaveBundle\Entity\LeaveCategory;
use Opit\OpitHrm\LeaveBundle\Entity\StatesLeaveRequests;
use Opit\Component\Utils\Utils;
use Opit\Component\Email\EmailManagerInterface;
use Opit\OpitHrm\LeaveBundle\Manager\LeaveNotificationManager;

/**
 * Description of LeaveRequestService
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveRequestService
{
    protected $securityContext;
    protected $entityManager;
    protected $statusManager;
    protected $mailer;
    protected $leaveNotificationManager;
    protected $leaveStatusManager;
    protected $options;

    /**
     *
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Opit\OpitHrm\LeaveBundle\Manager\LeaveStatusManager $statusManager
     * @param \Opit\Component\Email\EmailManagerInterface $mailer
     * @param \Opit\OpitHrm\LeaveBundle\Manager\LeaveNotificationManager $leaveNotificationManager
     */
    public function __construct(SecurityContext $securityContext, EntityManager $entityManager, LeaveStatusManager $statusManager, EmailManagerInterface $mailer, LeaveNotificationManager $leaveNotificationManager)
    {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->statusManager = $statusManager;
        $this->mailer = $mailer;
        $this->leaveNotificationManager = $leaveNotificationManager;
        $this->options['applicationName'];
    }

    /**
     * Method to set listing rights for the leave requests.
     *
     * @param object $leaveRequests
     * @return array
     */
    public function getLRStatusData($leaveRequests)
    {
        // Only usable if token is present
        $this->isAllowed();

        $currentStatusNames = array();
        $leaveRequestStates = array();
        $isForApproval = array();

        foreach ($leaveRequests as $leaveRequest) {
            $currentStatus = $this->statusManager->getCurrentStatus($leaveRequest);
            $currentStatusNames[$leaveRequest->getId()] = $currentStatus->getName();

            $leaveRequestStates[$leaveRequest->getId()] = $this->getNextAvailableStates($leaveRequest);

            if ($this->securityContext->isGranted('ROLE_ADMIN')) {
                $isForApproval[$leaveRequest->getId()] = false;
            } else {
                $isForApproval[$leaveRequest->getId()] = ($currentStatus->getId() === Status::FOR_APPROVAL);
            }
        }

        return array(
            'leaveRequestStates' => $leaveRequestStates,
            'currentStatusNames' => $currentStatusNames,
            'isForApproval' => $isForApproval
        );
    }

    /**
     * Check if leave request contains leave in the past
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return boolean
     */
    public function isLRPastLeaveDateDeleteable(LeaveRequest $leaveRequest)
    {
        foreach($leaveRequest->getLeaves() as $leave) {
            // Check if leave date is smaller then todays date
            if ($leave->getStartDate()->format('Y-m-d') <= date('Y-m-d')) {
                    return false;
            }
        }

        return true;
    }

    /**
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Opit\OpitHrm\StatusBundle\Entity\Status $currentStatus
     * @return array
     */
    public function setLeaveRequestAccessRights(LeaveRequest $leaveRequest, Status $currentStatus)
    {
        // Only usable if token is present
        $this->isAllowed();

        $isStatusLocked = true; // status can not be changed
        $unlockedStates = array();
        $currentEmployee = $this->securityContext->getToken()->getUser()->getEmployee();
        $isGeneralManager = $this->isUserGeneralManager($leaveRequest);

        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            if ($leaveRequest->getEmployee()->getId() === $currentEmployee->getId()) {
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
        } else {
            $isStatusLocked = false;
            if (in_array($currentStatus->getId(), array(Status::REJECTED, Status::APPROVED))) {
                $isStatusLocked = true;
            }
        }

        return $isStatusLocked;
    }

    /**
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return type
     */
    public function isUserGeneralManager(LeaveRequest $leaveRequest)
    {
        return $leaveRequest->getGeneralManager()->getEmployee()->getId() === $this->securityContext->getToken()->getUser()->getEmployee()->getId();
    }

    /**
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
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
     * @param \Opit\OpitHrm\LeaveBundle\Entity\Leave $leave
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveCategory $leaveCategory
     * @param integer $leaveLength
     * @return \Opit\OpitHrm\LeaveBundle\Entity\Leave
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
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param $leaveRequestGroup
     * @param \Opit\OpitHrm\UserBundle\Entity\Employee $employee
     * @return \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest
     */
    public function createLRInstance(LeaveRequest $leaveRequest, $leaveRequestGroup, Employee $employee, $isMassLeaveRequest = false)
    {
        $lr = new LeaveRequest();
        $lr->setEmployee($employee);
        $lr->setGeneralManager($leaveRequest->getGeneralManager());
        $lr->setTeamManager($leaveRequest->getTeamManager());
        $lr->setLeaveRequestGroup($leaveRequestGroup);
        $lr->setIsMassLeaveRequest($isMassLeaveRequest);

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

        $administrativeLeaves = $this->entityManager->getRepository('OpitOpitHrmLeaveBundle:LeaveDate')->getAdminLeavesInDateRange($startDate, $endDate);

        // Count administrative leaves
        foreach ($administrativeLeaves as $date) {
            if ($date['holidayDate']->format('D') != 'Sat' && $date['holidayDate']->format('D') != 'Sun') {
                $administrativeLeavesCount++;
            }
        }

        // Count administrative working days
        $administrativeWorkingDays = $this->entityManager->getRepository('OpitOpitHrmLeaveBundle:LeaveDate')->countLWDBWDateRange($startDate, $endDate, true);
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
        $applicationName = $this->options['applicationName'];
        $templateVariables = array();
        $templateVariables['leaveRequest'] = $leaveRequest;

        $this->mailer->setRecipient($recipient);

        if (null === $status) {
            $this->mailer->setSubject(
                '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - System leave requests created'
            );
        } else {
            $templateVariables['statusName'] = $status->getName();
            $templateVariables['isForApproval'] = Status::FOR_APPROVAL === $status->getId() ? true : false;

            $this->mailer->setSubject(
                '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - System leave request - ' . $status->getName() . ' (' . $leaveRequest->getLeaveRequestId() . ')'
            );
        }
        $this->mailer->setBodyByTemplate('OpitOpitHrmLeaveBundle:Mail:massLeaveRequests.html.twig', array_merge($templateVariables, $unpaidLeaveDetails));
        $this->mailer->sendMail();
    }

    /**
     * Check leave requests date overlapping
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return array of date overlappings
     */
    public function checkLRsDateOverlapping($leaveRequest, $employees)
    {
        $result = array();
        $employeeLeaveRequests = array();
        // If this is an own employee leave request.
        if (empty($employees)) {
            $employees[] = $this->securityContext->getToken()->getUser()->getEmployee();
        }
        // Get the employee lave requests.
        foreach ($employees as $employee) {
            $employeeLeaveRequests[] = $this->entityManager->getRepository('OpitOpitHrmLeaveBundle:LeaveRequest')->findBy(array(
                'employee' => $this->entityManager->getRepository('OpitOpitHrmUserBundle:Employee')->find($employee)
            ));
        }
        // Check the date overlappings.
        foreach ($employeeLeaveRequests as $leaveRequests) {
            foreach ($leaveRequests as $lr) {
                if ($leaveRequest !== $lr) {
                    // Compare the date overlapping between leave requests
                    $dateOverlappings = $this->compareLRDateOverlapping($leaveRequest, $lr);
                    if (0 < count($dateOverlappings)) {
                        $result[] = $dateOverlappings;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Compare the date overlapping between leave requests
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $currentLR
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $otherLR
     * @return array of date overlappings
     */
    public function compareLRDateOverlapping($currentLR, $otherLR)
    {
        $dateOverlappings = array();
        // Iterate the current leave request's leaves.
        foreach ($currentLR->getLeaves() as $currentElement) {
            // Iterate the other leave request' leaves.
            foreach ($otherLR->getLeaves() as $otherElement) {
                // Checking the date overlapping.
                if (($currentElement->getStartDate() <= $otherElement->getEndDate()) &&
                    ($otherElement->getStartDate() <= $currentElement->getEndDate())) {
                    $dateOverlappings[$otherLR->getLeaveRequestId()] = array(
                        'startDate' => $otherElement->getStartDate(),
                        'endDate' => $otherElement->getEndDate()
                    );
                    break;
                }
            }
        }

        return $dateOverlappings;
    }

    /**
     * Reject the leave requests related to leaves, send a notification and email about it
     *
     * @param array $leaves
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $lr
     */
    public function rejectLeavesLRs(array $leaves, LeaveRequest $lr)
    {
        foreach ($leaves as $leave) {
            $leaveRequest = $leave->getLeaveRequest();
            $leaveRequest->setIsOverlapped(true);
            $leaveRequest->setRejectedGmName($lr->getGeneralManager()->getEmployee()->getEmployeeName());
            $status = $this->entityManager->getRepository('OpitOpitHrmStatusBundle:Status')->find(Status::REJECTED);
            $this->leaveNotificationManager->addNewLeaveNotification($leaveRequest, false, $status);
            $this->statusManager->addStatus($leaveRequest, Status::REJECTED);
        }
    }

    /**
     * Removes related leave request leave instances.
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param ArrayCollection $children
     */
    public function removeChildNodes(LeaveRequest $leaveRequest, $children)
    {
        foreach ($children as $child) {
            if (false === $leaveRequest->getLeaves()->contains($child)) {
                $child->setLeaveRequest();
                $this->entityManager->remove($child);
            }
        }
    }

    /**
     * Method to reject overlapping leaves leave request
     *
     * @param array $overlappingLeaves
     */
    public function rejectOverlappingLeavesLR(array $overlappingLeaves)
    {
        $statusRejected = $this->entityManager->getRepository('OpitOpitHrmStatusBundle:Status')->find(Status::REJECTED);

        foreach ($overlappingLeaves as $overlappingLeave) {
            $overlappingLeaveLR = $overlappingLeave->getLeaveRequest();

            $statesLeaveRequests = new StatesLeaveRequests();
            $statesLeaveRequests->setLeaveRequest($overlappingLeaveLR);
            $statesLeaveRequests->setStatus($statusRejected);

            $overlappingLeaveLR->addState($statesLeaveRequests);

            $this->entityManager->persist($statesLeaveRequests);
            $this->entityManager->persist($overlappingLeave);
        }
    }

    protected function isAllowed()
    {
        $token = $this->securityContext->getToken();

        if (null === $token) {
            throw new Exception\LeaveRequestServiceException('The security context contains no authentication token. This service cannot be used.');
        }
    }
}
