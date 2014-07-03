<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\LeaveBundle\Form\LeaveRequestType;
use Opit\Notes\LeaveBundle\Entity\LeaveRequest;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Component\Utils\Utils;
use Opit\Notes\LeaveBundle\Entity\Leave;
use Opit\Notes\LeaveBundle\Model\LeaveRequestService;
use Opit\Notes\LeaveBundle\Entity\LeaveCategory;
use Opit\Notes\LeaveBundle\Entity\LeaveRequestGroup;
use Opit\Notes\UserBundle\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Opit\Notes\LeaveBundle\Entity\StatesLeaveRequests;

class LeaveController extends Controller
{

    /**
     * To list leaves in Notes
     *
     * @Route("/secured/leave/list", name="OpitNotesLeaveBundle_leave_list")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function listLeaveRequestsAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $isGeneralManager = $securityContext->isGranted('ROLE_GENERAL_MANAGER');
        $employee = $user->getEmployee();
        $isSearch = $request->request->get('issearch');
        $searchRequests = array();
        $parentsOfGroupLRs = array();

        // Calculating the leave days for the current employee.
        $leaveCalculationService = $this->get('opit_notes_leave.leave_calculation_service');
        $leaveDays = $leaveCalculationService->leaveDaysCalculationByEmployee($this->getUser()->getEmployee());


        $config = $this->container->getParameter('pager_config');
        $maxResults = $config['max_results'];
        $offset = $request->request->get('offset');
        $pagnationParameters = array(
            'firstResult' => ($offset * $maxResults),
            'maxResults' => $maxResults,
            'isAdmin' => $securityContext->isGranted('ROLE_ADMIN'),
            'isGeneralManager' => $securityContext->isGranted('ROLE_GENERAL_MANAGER'),
            'employee' => $employee,
            'user' => $user
        );

        if ($isSearch) {
            $searchRequests = $request->request->all();
        }

        $leaveRequests = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')
            ->findAllByFiltersPaginated($pagnationParameters, $searchRequests);

        // Set parent leave request ids
        foreach ($leaveRequests as $leaveRequest) {
            if ($lrg = $leaveRequest->getLeaveRequestGroup()) {
                $massLeaveRequest = $lrg->getLeaveRequests(array('isMassLeaveRequest' => 1), array('limit' => 1));
                if (!$leaveRequest->getIsMassLeaveRequest()) {
                     $leaveRequest->setParentLeaveRequestId($massLeaveRequest[0]->getLeaveRequestId());
                }
            }
        }

        $listingRights = $this->get('opit.model.leave_request')
            ->setLeaveRequestListingRights($leaveRequests, $user);

        if ($request->request->get('resetForm') || $isSearch || null !== $offset) {
            $template = 'OpitNotesLeaveBundle:Leave:_list.html.twig';
        } else {
            $template = 'OpitNotesLeaveBundle:Leave:list.html.twig';
        }

        return $this->render(
                $template, array(
                'leaveRequests' => $leaveRequests,
                'leaveDays' => $leaveDays,
                'numberOfPages' => ceil(count($leaveRequests) / $maxResults),
                'offset' => ($offset + 1),
                'maxPages' => $config['max_pages'],
                'listingRights' => $listingRights,
                'isGeneralManager' => $isGeneralManager
                )
        );
    }

    /**
     * To add/edit leave in Notes
     *
     * @Route("/secured/leave/show/{id}", name="OpitNotesLeaveBundle_leave_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_USER")
     * @throws CreateNotFoundException
     * @Template()
     */
    public function showLeaveRequestAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $leaveRequestId = $request->attributes->get('id');
        $isNewLeaveRequest = 'new' === $leaveRequestId ? true : false;
        $securityContext = $this->container->get('security.context');
        $employee = $securityContext->getToken()->getUser()->getEmployee();
        $leaveRequestService = $this->get('opit.model.leave_request');
        $errors = array();
        $isGeneralManager = $securityContext->isGranted('ROLE_GENERAL_MANAGER');
        $unpaidLeaveDetails = array();
        $requestFor = null;

        if ($isNewLeaveRequest) {
            $leaveRequest = new LeaveRequest();
            $leaveRequest->setEmployee($employee);
        } else {
            $leaveRequest = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->find($leaveRequestId);

            if (null === $leaveRequest) {
                throw $this->createNotFoundException('Missing leave request.');
            }

            if ($employee !== $leaveRequest->getEmployee() && !$isGeneralManager) {
                throw new AccessDeniedException(
                'Access denied for leave request ' . $leaveRequest->getLeaveRequestId()
                );
            }
        }

        $leaveRequest->setIsCreatedByGM($isGeneralManager);
        $statusManager = $this->get('opit.manager.leave_status_manager');
        $currentStatus = $statusManager->getCurrentStatus($leaveRequest);
        $leaveRequestStates = $statusManager->getNextStates($currentStatus);

        $children = new ArrayCollection();
        $form = $this->createForm(
            new LeaveRequestType($isNewLeaveRequest), $leaveRequest, array('em' => $entityManager)
        );

        if (null !== $leaveRequest) {
            foreach ($leaveRequest->getLeaves() as $leave) {
                $children->add($leave);
            }
        }

        if ($request->isMethod("POST")) {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $employees = $request->request->get('employee');
                $isLRCreatedByGM = null !== $request->request->get('leave-request-owner');

                if (1 === count($employees) || null === $request->request->get('leave-request-owner') || 'own' === $request->request->get('leave-request-owner')) {
                    if ($isLRCreatedByGM) {
                        if (!is_array($employees)) {
                            $employees = array($employee);
                        }
                    } else {
                        $employees = array($employee);
                    }
                    // Single LR is being created
                    $error = $this->createLeaveRequests($leaveRequest, $employees);
                } elseif (count($employees) > 1) {
                    // MLR is being created
                    $error = $this->createLeaveRequests($leaveRequest, $employees, true);
                } else {
                    // No employee was passed while creating MLR
                    $form->addError(new FormError('No employees are selected for mass leave request.'));
                }

                if (null !== $error) {
                    $form->addError(new FormError($error));
                    $errors = Utils::getErrorMessages($form);
                } else {
                    return $this->redirect($this->generateUrl('OpitNotesLeaveBundle_leave_list'));
                }
            } else {
                $requestFor = $request->request->get('leave-request-owner');
                $errors = Utils::getErrorMessages($form);
            }
        }
        $isForApproval = $currentStatus->getId() === Status::FOR_APPROVAL;

        return $this->render(
            'OpitNotesLeaveBundle:Leave:showLeaveRequest.html.twig', array_merge(
                array(
                    'form' => $form->createView(),
                    'isNewLeaveRequest' => $isNewLeaveRequest,
                    'leaveRequestStates' => $leaveRequestStates,
                    'leaveRequest' => $leaveRequest,
                    'errors' => $errors,
                    'isGeneralManager' => $isGeneralManager,
                    'unpaidLeaveDetails' => $unpaidLeaveDetails,
                    'isForApproval' => $isForApproval,
                    'requestFor' => $requestFor,
                    'isLRLocked' => (Status::REJECTED === $statusManager->getCurrentStatus($leaveRequest))
                ),
                $isNewLeaveRequest ? array('isStatusLocked' => true,
                    'isEditLocked' => false) : $leaveRequestService->setLeaveRequestAccessRights($leaveRequest, $currentStatus),
                $isGeneralManager ? array('employees' => $entityManager->getRepository('OpitNotesUserBundle:Employee')->findAll()) : array()
            )
        );
    }

    /**
     * To generate details form for leave requests
     *
     * @Route("/secured/leave/show/details", name="OpitNotesLeaveBundle_leave_show_details")
     * @Template()
     */
    public function showDetailsAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $leaveRequestId = $request->request->get('id');
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager->getFilters()->disable('softdeleteable');
        // For creating entities for the leave request preview
        $leaveRequest = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->find($leaveRequestId);

        if (null === $leaveRequest) {
            throw $this->createNotFoundException('Missing leave request.');
        }

        $children = new ArrayCollection();
        // Add the leaves to leave reqeust.
        if (null !== $leaveRequest) {
            foreach ($leaveRequest->getLeaves() as $leave) {
                $children->add($leave);
            }
        }

        // Calculating the leave days for the current employee.
        $leaveCalculationService = $this->get('opit_notes_leave.leave_calculation_service');
        $leaveDays = $leaveCalculationService->leaveDaysCalculationByEmployee($this->getUser()->getEmployee());

        return $this->render(
                'OpitNotesLeaveBundle:Leave:showDetails.html.twig', array(
                'leaveRequest' => $leaveRequest,
                'leaveDays' => $leaveDays
                )
        );
    }

    /**
     * To delete leave request in Notes
     *
     * @Route("/secured/leaverequest/delete", name="OpitNotesLeaveBundle_leaverequest_delete")
     * @Secure(roles="ROLE_USER")
     * @throws AccessDeniedException
     * @Template()
     * @Method({"POST"})
     */
    public function deleteLeaveRequestAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        $leaveRequestService = $this->get('opit.model.leave_request');
        $currentUser = $securityContext->getToken()->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $ids = $request->request->get('id');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $leaveRequest = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->find($id);

            if ($currentUser->getEmployee() !== $leaveRequest->getEmployee() &&
                !$this->get('security.context')->isGranted('ROLE_ADMIN') &&
                !$this->get('security.context')->isGranted('ROLE_GENERAL_MANAGER') &&
                $leaveRequest->getCreatedUser()->getId() !== $currentUser->getId()) {
                throw new AccessDeniedException(
                'Access denied for leave.'
                );
            }

            // If it is a massive leave request then delete all child employee leave requests.
            if (true === $leaveRequest->getIsMassLeaveRequest()) {
                // Remove the leave request group.
                // This will remove the joined request leaves too.
                $entityManager->remove($leaveRequest->getLeaveRequestGroup());
            } elseif ($leaveRequestService->isLeaveRequestDeleteable($leaveRequest, $currentUser)) {
                $entityManager->remove($leaveRequest);
            }
        }

        $entityManager->flush();

        return new JsonResponse('success');
    }

    /**
     * Method to change state of leave request
     *
     * @Route("/secured/leave/state/change", name="OpitNotesLeaveBundle_leave_request_state")
     * @Secure(roles="ROLE_USER")
     * @Method({"POST"})
     * @Template()
     */
    public function changeLeaveRequestStateAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = $request->request->get('status');
        $leaveRequest = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')
            ->find($data['foreignId']);

        // Set comment content or null
        $comment = isset($data['comment']) && $data['comment'] ? $data['comment'] : null;

        return $this->get('opit.manager.leave_status_manager')
                ->changeStatus($leaveRequest, $data['id'], false, $comment);
    }

    /**
     * To send employee leave summary on Info Board
     *
     * @Route("/secured/leaves/employeesummary", name="OpitNotesLeaveBundle_leaves_employeesummary")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function employeeLeavesinfoBoardAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $leaveRequestRepository = $em->getRepository('OpitNotesLeaveBundle:LeaveRequest');
        $employeeID = $user->getEmployee()->getID();
        $yearFirstDate = date(date('Y') . '-01' . '-01');
        $yearLastDate = date(date('Y') . '-12' . '-31');

        // entitled leaves count
        $leaveCalculationService = $this->get('opit_notes_leave.leave_calculation_service');
        $empLeaveEntitlement = $leaveCalculationService->leaveDaysCalculationByEmployee($user->getEmployee());

        //get leave categories
        $leaveCategories = $em->getRepository('OpitNotesLeaveBundle:LeaveCategory')->findAll();

        //total leave request count
        $totalLeaveRequestCount = $leaveRequestRepository->findEmployeesLRCount($employeeID, $yearFirstDate, $yearLastDate);

        //finalized leave request count
        $finalizedLeaveRequestCount = $leaveRequestRepository->findEmployeesLRCount($employeeID, $yearFirstDate, $yearLastDate, true);

        //pending leave request count
        $pendingLeaveRequestCount = $totalLeaveRequestCount - $finalizedLeaveRequestCount;

        //total applied leaves count
        $totalAppliedLeaveDays = ($leaveRequestRepository->totalCountedLeaveDays($employeeID, true) ? $leaveRequestRepository->totalCountedLeaveDays($employeeID, true) : 0);

        //entitled leave days
        $entitledAppliedLeaveDays = $leaveRequestRepository->totalCountedLeaveDays($employeeID);

        //not entitled leave days count
        $notEntitledAppliedLeaveDays = $totalAppliedLeaveDays - $entitledAppliedLeaveDays;

        //left to avail
        $leftToAvail = ($empLeaveEntitlement > $entitledAppliedLeaveDays ? $empLeaveEntitlement - $entitledAppliedLeaveDays : 0);

        return $this->render(
                'OpitNotesLeaveBundle:Leave:_employeeLeavesinfoBoard.html.twig', array(
                'empLeaveEntitlement' => $empLeaveEntitlement,
                'leaveCategories' => $leaveCategories,
                'pendingLeaveRequestCount' => $pendingLeaveRequestCount,
                'finalizedLeaveRequestCount' => $finalizedLeaveRequestCount,
                'leftToAvail' => $leftToAvail,
                'totalAppliedLeaveDays' => $totalAppliedLeaveDays,
                'entitledAppliedLeaveDays' => $entitledAppliedLeaveDays,
                'totalLeaveRequestCount' => $totalLeaveRequestCount,
                'notEntitledAppliedLeaveDays' => $notEntitledAppliedLeaveDays
                )
        );
    }

    /**
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param array $employees
     * @param type $isMLR
     * @return string
     */
    protected function createLeaveRequests(LeaveRequest $leaveRequest, array $employees, $isMLR = false)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $overlappingLeaves = array();
        $now = new \DateTime();

        // Get all leaves that are overlapping
        foreach ($leaveRequest->getLeaves() as $leave) {
            $leaveStartDate = $leave->getStartDate();
            $leaveEndDate = $leave->getEndDate();
            // Check if MLR is being created in the past
            if ($isMLR && ($leaveStartDate < $now || $leaveEndDate < $now)) {
                return 'You can not create LR for more than one employee in the past.';
            } else {
                $overlappingLeaves[] = $entityManager->getRepository('OpitNotesLeaveBundle:Leave')->findOverlappingLeavesByDatesEmployees(
                    $leaveStartDate, $leaveEndDate, $employees
                );
            }
        }

        // Check if single leave request has any leaves in the past overlapping
        if (!$isMLR && count($overlappingLeaves[0]) > 0) {
            return 'Can not create LR. Employee has already taken leave during this period.';
        }

        $leaveCalculationService = $this->get('opit_notes_leave.leave_calculation_service');

        $fullDayCategory = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveCategory')->findOneByName(LeaveCategory::FULL_DAY);
        $statusRejected = $entityManager->getRepository('OpitNotesStatusBundle:Status')->find(Status::REJECTED);

        if ($isMLR) {
            $leaveRequestGroup = $this->createLRGroup($leaveRequest, $entityManager, $fullDayCategory);
        }

        foreach ($employees as $employee) {
            $employee = $entityManager->getRepository('OpitNotesUserBundle:Employee')->find($employee);
            // Leave entitlement for an employee
            $leaveEntitlement = $leaveCalculationService->leaveDaysCalculationByEmployee($employee);

            // Employees availed leave days
            $employeeAvailedLeaveDays = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->totalCountedLeaveDays($employee->getId());

            // Employee left to avail days
            $leftToAvail = $leaveEntitlement - $employeeAvailedLeaveDays;

            $data = array(
                'fullDayCategory' => $fullDayCategory,
                'unpaidCategory' => $entityManager->getRepository('OpitNotesLeaveBundle:LeaveCategory')->findOneByName(LeaveCategory::UNPAID),
                'approvedStatus' => $entityManager->getRepository('OpitNotesStatusBundle:Status')->find(Status::APPROVED),
                'forApprovalStatus' => $entityManager->getRepository('OpitNotesStatusBundle:Status')->find(Status::FOR_APPROVAL),
            );

            if ($isMLR) {
                // Change status of all overlapping leave requests to rejected
                foreach ($overlappingLeaves[0] as $overlappingLeave) {
                    $overlappingLeaveLR = $overlappingLeave->getLeaveRequest();

                    $statesLeaveRequests = new StatesLeaveRequests();
                    $statesLeaveRequests->setLeaveRequest($overlappingLeaveLR);
                    $statesLeaveRequests->setStatus($statusRejected);

                    $overlappingLeaveLR->addState($statesLeaveRequests);

                    $entityManager->persist($statesLeaveRequests);
                    $entityManager->persist($overlappingLeave);
                }

                $this->createMLR($leaveRequest, $entityManager, $leaveRequestGroup, $leftToAvail, $employee, $data);
            } else {
                $error = $this->createSingleLR($leaveRequest, $entityManager, $data, $leftToAvail, $employee);
                if (null !== $error) {
                    return $error;
                }
            }
        }
        $entityManager->flush();
    }

    /**
     * Method to create mass leave request
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequestGroup $leaveRequestGroup
     * @param type $leftToAvail
     * @param type $employee
     * @param type $data
     */
    protected function createMLR(LeaveRequest $leaveRequest, EntityManagerInterface $entityManager, LeaveRequestGroup $leaveRequestGroup, $leftToAvail, $employee, $data)
    {
        $leaveRequestService = $this->get('opit.model.leave_request');

        $leave = current(current($leaveRequest->getLeaves()));
        $leaveRequest->setEmployee($employee);

        // Create new instance of leave request and leave not to overwrite old record in db
        $lr = $leaveRequestService->createLRInstance($leaveRequest, $leaveRequestGroup, $employee, false);
        $l = $leaveRequestService->createLeaveInstance(
            $leave, $lr, $data['unpaidCategory'], $leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate()), $leave->getStartDate(), $leave->getEndDate()
        );
        $lr->addLeaf($l);
        $lr->setEmployee($employee);

        // Get total number of working days between two dates
        $countLeaveDays = $leaveRequestService->countLeaveDays($l->getStartDate(), $l->getEndDate());

        // Check if leave length is bigger than the days left to avail for employee
        if ($countLeaveDays > $leftToAvail) {
            // If employee has no days left to avail
            if (0 === $leftToAvail) {
                $l->setCategory($data['unpaidCategory']);
            } else {
                $endDate = $this->calculateLeaveEndDate($l, $leaveRequestService, $leftToAvail);
                $leaveEndDate = clone $l->getEndDate();
                $leaveStartDate = date_add(clone $endDate, date_interval_create_from_date_string('1 day'));
                $LRForApproval = $leaveRequestService->createLRInstance($lr, $leaveRequestGroup, $employee, false);
                $LRForApprovalLeave = $leaveRequestService->createLeaveInstance(
                    $l, $lr, $data['unpaidCategory'], $leaveRequestService->countLeaveDays($leaveStartDate, $leaveEndDate), $leaveStartDate, $leaveEndDate
                );

                $LRForApproval->addLeaf($LRForApprovalLeave);
                $LRForApproval->setEmployee($employee);
                $entityManager->persist($LRForApprovalLeave);
                $entityManager->persist($LRForApproval);
                $this->setLRStatusSendNotificationEmail($LRForApproval, $employee, $data['forApprovalStatus'], $leaveRequestService);

                $l->setEndDate($endDate);
                $l->setNumberOfDays($leaveRequestService->countLeaveDays($l->getStartDate(), $l->getEndDate()));
                $l->setCategory($data['fullDayCategory']);
            }
        } else {
            $l->setCategory($data['fullDayCategory']);
            $l->setNumberOfDays(
                $leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate())
            );
        }

        $entityManager->persist($l);
        $entityManager->persist($lr);
        $this->setLRStatusSendNotificationEmail($lr, $employee, $data['approvedStatus'], $leaveRequestService);
    }

    /**
     * Method to create single leave request (own or single employee selected)
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param type $data
     * @param type $leftToAvail
     * @return string|null
     */
    protected function createSingleLR(LeaveRequest $leaveRequest, EntityManagerInterface $entityManager, $data, $leftToAvail, $employee)
    {
        $leaveRequestService = $this->get('opit.model.leave_request');
        $leaveRequest->setEmployee($employee);
        $totalLeaveDaysCount = 0;
        $isValid = true;

        foreach ($leaveRequest->getLeaves() as $leave) {
            $countLeaveDays = $leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate());

            // Set leave category if it has none
            if (null === $leave->getCategory()) {
                $leave->setCategory($data['fullDayCategory']);
            }

            // Check if leave is unpaid
            if ($data['unpaidCategory']->getId() !== $leave->getCategory()->getId()) {
                // Get total number of working days between two dates
                $totalLeaveDaysCount += $countLeaveDays;
            }

            if ($totalLeaveDaysCount > $leftToAvail) {
                $isValid = false;
            } else {
                $leave->setNumberOfDays($countLeaveDays);
                $entityManager->persist($leave);
            }
        }

        if ($isValid) {
            $entityManager->persist($leaveRequest);
            $status = $data['approvedStatus'];
            $securityContext = $this->container->get('security.context');
            $employeeId = $securityContext->getToken()->getUser()->getEmployee()->getId();
            if ($employeeId === $employee->getId()) {
                $status = $data['forApprovalStatus'];
            }
            $this->setLRStatusSendNotificationEmail($leaveRequest, $leaveRequest->getEmployee(), $status, $leaveRequestService);
        } else {
            return 'Employee has no more days left to avail.';
        }

        return null;
    }

    /**
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveCategory $fullDayCategory
     * @return \Opit\Notes\LeaveBundle\Entity\LeaveRequestGroup
     */
    protected function createLRGroup(LeaveRequest $leaveRequest, EntityManagerInterface $entityManager, LeaveCategory $fullDayCategory)
    {
        $leave = current(current($leaveRequest->getLeaves()));
        $leaveRequestService = $this->get('opit.model.leave_request');
        $leaveRequestGroup = new LeaveRequestGroup();
        $entityManager->persist($leaveRequestGroup);

        // Create mass leave request
        $massLeaveRequest = $leaveRequestService->createLRInstance(
            $leaveRequest, $leaveRequestGroup, $this->get('security.context')->getToken()->getUser()->getEmployee(), true
        );

        // Create new instance leave for massive leaveRequest
        $leaveOfMLR = $leaveRequestService->createLeaveInstance($leave, $massLeaveRequest, $fullDayCategory, 0, $leave->getStartDate(), $leave->getEndDate());
        $leaveOfMLR->setNumberOfDays($leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate()));
        $massLeaveRequest->addLeaf($leaveOfMLR);
        $entityManager->persist($massLeaveRequest);

        return $leaveRequestGroup;
    }

    /**
     * Set the status of the leave request, send an email about its summary and set the notification for it
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $lr
     * @param \Opit\Notes\UserBundle\Entity\Employee $employee
     * @param \Opit\Notes\StatusBundle\Entity\Status $status
     * @param \Opit\Notes\LeaveBundle\Model\LeaveRequestService $leaveRequestService
     */
    protected function setLRStatusSendNotificationEmail(LeaveRequest $lr, Employee $employee, Status $status, LeaveRequestService $leaveRequestService)
    {
        $this->get('opit.manager.leave_status_manager')->forceStatus($status->getId(), $lr, $lr->getGeneralManager());
        $leaveRequestService->prepareMassLREmail($lr, $employee->getUser()->getEmail(), array(), $status);

        // set a notification to the employee about the leave request
        $this->get('opit.manager.leave_notification_manager')->addNewLeaveNotification($lr, false, $status);
    }

    /**
     * Get the last day from date range
     *
     * @param \Opit\Notes\LeaveBundle\Entity\Leave $leave
     * @param \Opit\Notes\LeaveBundle\Model\LeaveRequestService $leaveRequestService
     * @param integer $leftToAvail
     * @return DateTime
     */
    protected function calculateLeaveEndDate(Leave $leave, LeaveRequestService $leaveRequestService, $leftToAvail)
    {
        $leaveStartDate = $leave->getStartDate();
        $leaveEndDate = clone $leaveStartDate;
        $countLeaveDays = $leaveRequestService->countLeaveDays($leaveStartDate, $leaveEndDate);
        while ($countLeaveDays !== $leftToAvail) {
            $leaveEndDate = date_add($leaveEndDate, date_interval_create_from_date_string('1 day'));
            $countLeaveDays = $leaveRequestService->countLeaveDays($leaveStartDate, $leaveEndDate);
        }

        return $leaveEndDate;
    }

    /**
     * Check if leave category can be selected using the left to availed days,
     * add error message to leave category if days left to avail were exceeded.
     *
     * @param \Opit\Notes\UserBundle\Entity\Employee $employee
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param \Opit\Notes\LeaveBundle\Model\LeaveRequestService $leaveRequestService
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param type $form
     */
    protected function validateLeaveDatesCategory(LeaveRequest $leaveRequest, LeaveRequestService $leaveRequestService, EntityManagerInterface $entityManager, $form)
    {
        $leaveCalculationService = $this->get('opit_notes_leave.leave_calculation_service');

        $employee = $leaveRequest->getEmployee();

        // Leave entitlements of an employee.
        $leaveEntitlement = $leaveCalculationService->leaveDaysCalculationByEmployee($employee);

        // Availed leave days of an employee.
        $employeeAvailedLeaveDays = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->totalCountedLeaveDays($employee->getId());

        // Left to avail days of an employee.
        $leftToAvail = $leaveEntitlement - $employeeAvailedLeaveDays;

        $countLeaveDays = 0;

        $leaves = $leaveRequest->getLeaves();

        $message = 'Entitlement exceeded - kindly change category';

        // Loop through all leaves employee has posted.
        foreach ($leaves as $index => $leave) {
            if (LeaveCategory::UNPAID !== $leave->getCategory()->getName()) {
                $countLeaveDays += $leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate());
                // Check if count of leave days are more than days left to avail.
                if ($countLeaveDays > $leftToAvail) {
                    // If there are days left to avail
                    if ($leftToAvail > 0) {
                        // Add error to leave category
                        $form->get('leaves')->get($index)->get('category')->addError(new FormError($message . ' or dates.'));
                        $leftToAvail = 0;
                    }

                    // Add error to leave category
                    $form->get('leaves')->get($index)->get('category')->addError(new FormError($message . '.'));
                }
            }
        }
    }

}
