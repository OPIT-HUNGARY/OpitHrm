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

        $massLeaveRequests = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')
            ->findBy(array('isMassLeaveRequest' => 1));
        foreach ($massLeaveRequests as $massLeaveRequest) {
            $parentsOfGroupLRs[$massLeaveRequest->getLeaveRequestGroup()->getId()] = $massLeaveRequest;
        }

        $listingRights = $this->get('opit.model.leave_request')
            ->setLeaveRequestListingRights($leaveRequests, $user);

        if ($request->request->get('resetForm') || $isSearch || null !== $offset) {
            $template = 'OpitNotesLeaveBundle:Leave:_list.html.twig';
        } else {
            $template = 'OpitNotesLeaveBundle:Leave:list.html.twig';
        }

        return $this->render(
            $template,
            array(
                'leaveRequests' => $leaveRequests,
                'parentsOfGroups' => $parentsOfGroupLRs,
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
            $employees = $request->request->get('employee');
            $isOwn =  'own' === $request->request->get('leave-request-owner') ? true : false;

            // If it is an employee request.
            if ($isOwn) {
                $this->validateLeaveDatesCategory($leaveRequest, $leaveRequestService, $entityManager, $form);
            } elseif (!$isOwn && empty($employees)) {
                // If it is an mass leave request and the employees is empty then add a form error.
                $form->addError(new FormError('The employees are not selected for the mass leave request.'));
            }

            if ($form->isValid()) {
                if ($isGeneralManager && count($employees) > 0) {
                    // Creating mass leave requests.
                    $unpaidLeaveDetails = $this->createEmployeeLeaveRequests($leaveRequest, $entityManager, $employees);

                    if (empty($unpaidLeaveDetails)) {
                        return $this->redirect($this->generateUrl('OpitNotesLeaveBundle_leave_list'));
                    }
                } else {
                    // Check the date overlappling with previous leave requests
                    $dateOverlappings = $leaveRequestService->checkLRsDateOverlapping($leaveRequest, $employees);
                    // If there are not any date overlappings with other LRs this LR
                    if (0 === count($dateOverlappings)) {
                        // Creating single leave request.
                        foreach ($children as $child) {
                            if (false === $leaveRequest->getLeaves()->contains($child)) {
                                $child->setLeaveRequest();
                                $entityManager->remove($child);
                            }
                        }

                        foreach ($leaveRequest->getLeaves() as $leave) {
                            $leave->setNumberOfDays(
                                $leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate())
                            );
                        }

                        $entityManager->persist($leaveRequest);
                        $entityManager->flush();

                        if ($isNewLeaveRequest) {
                            $statusManager->changeStatus($leaveRequest, Status::CREATED, true);
                        }

                        return $this->redirect($this->generateUrl('OpitNotesLeaveBundle_leave_list'));
                    } else {
                        // Set the date collisions into the error array.
                        foreach ($dateOverlappings as $dateOverlapping) {
                            foreach ($dateOverlapping as $requestId => $dates) {
                                $error = sprintf(
                                    'Leave dates overlapping with leave request %s and dates %s - %s', $requestId,
                                    $dates['startDate']->format('Y-m-d'),
                                    $dates['endDate']->format('Y-m-d')
                                );
                                $form->addError(new FormError($error));
                            }
                        }
                    }
                }
            } else {
                $requestFor = $request->request->get('leave-request-owner');
                $errors = Utils::getErrorMessages($form);
            }
        }
        $isForApproval = $currentStatus->getId() === Status::FOR_APPROVAL;

        return $this->render(
            'OpitNotesLeaveBundle:Leave:showLeaveRequest.html.twig',
            array_merge(
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
                $isNewLeaveRequest ? array('isStatusLocked' => true, 'isEditLocked' => false) : $leaveRequestService->setLeaveRequestAccessRights($leaveRequest, $currentStatus),
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
            'OpitNotesLeaveBundle:Leave:showDetails.html.twig',
            array(
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
        $notEntitledAppliedLeaveDays = $totalAppliedLeaveDays -  $entitledAppliedLeaveDays;

        //left to avail
        $leftToAvail = ($empLeaveEntitlement > $entitledAppliedLeaveDays ? $empLeaveEntitlement - $entitledAppliedLeaveDays : 0);

        return $this->render(
            'OpitNotesLeaveBundle:Leave:_employeeLeavesinfoBoard.html.twig',
            array(
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
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param array $employees
     * @return array
     */
    protected function createEmployeeLeaveRequests(LeaveRequest $leaveRequest, EntityManagerInterface $entityManager, array $employees)
    {
        $leaveRequestService = $this->get('opit.model.leave_request');
        $currentLeave = current(current($leaveRequest->getLeaves()));
        // find leaves that are overlapping
        $overlappingLeaves = $entityManager->getRepository('OpitNotesLeaveBundle:Leave')->findOverlappingLeavesByDatesEmployees(
            $currentLeave->getStartDate(), $currentLeave->getEndDate(), $employees
        );
        // reject all overlapping LRs and send notification, email
        $leaveRequestService->rejectLeavesLRs($overlappingLeaves, $leaveRequest);

        $leaveCalculationService = $this->get('opit_notes_leave.leave_calculation_service');
        $unpaidLeaveDetails = array();
        $unpaidLeaveLength = 0;

        $fullDayCategory = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveCategory')->findOneByName(LeaveCategory::FULL_DAY);
        $unpaidCategory = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveCategory')->findOneByName(LeaveCategory::UNPAID);

        $statusApproved = $entityManager->getRepository('OpitNotesStatusBundle:Status')->find(Status::APPROVED);
        $statusForApproval = $entityManager->getRepository('OpitNotesStatusBundle:Status')->find(Status::FOR_APPROVAL);

        $leaveRequestGroup = new LeaveRequestGroup();
        $entityManager->persist($leaveRequestGroup);

        // get leave from leave request
        $leave = current(current($leaveRequest->getLeaves()));
        $startDate = clone $leave->getStartDate();
        $endDate = clone $leave->getEndDate();

        // Create mass leave request
        $massLeaveRequest = $leaveRequestService->createLRInstance(
            $leaveRequest, $leaveRequestGroup, $this->get('security.context')->getToken()->getUser()->getEmployee(), true
        );
        // create new instance leave for massive leaveRequest
        $leaveOfMLR = $leaveRequestService->createLeaveInstance($leave, $massLeaveRequest, $fullDayCategory, 0, $startDate, $endDate);
        $leaveOfMLR->setNumberOfDays($leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate()));
        $massLeaveRequest->addLeaf($leaveOfMLR);
        $entityManager->persist($massLeaveRequest);

        foreach ($employees as $employee) {
            $employee = $entityManager->getRepository('OpitNotesUserBundle:Employee')->find($employee);

            // create new leave request instace to not overwrite old one
            $lr = $leaveRequestService->createLRInstance($leaveRequest, $leaveRequestGroup, $employee);

            // create new instance of leave
            $leave = $leaveRequestService->createLeaveInstance($leave, $lr, $fullDayCategory, 0, $startDate, $endDate);

            // leave entitlement for an employee
            $leaveEntitlement = $leaveCalculationService->leaveDaysCalculationByEmployee($employee);

            // employees availed leave days
            $employeeAvailedLeaveDays = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->totalCountedLeaveDays($employee->getId());

            // employee left to avail days
            $leftToAvail = $leaveEntitlement - $employeeAvailedLeaveDays;

            $countLeaveDays = $leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate());

            // if the number of leave days are bigger than the days left to avail for the employee
            if ($countLeaveDays > $leftToAvail) {
                // if employee has days left to avail
                if (0 < $leftToAvail) {
                    // calculate the end date of the leave using the days left to avail
                    $leaveEndDate = $this->calculateLeaveEndDate($leave, $leaveRequestService, $leftToAvail);

                    // assign current end date of leave to a variable
                    $leaveForApprovalEndDate = clone $leave->getEndDate();
                    $leave->setEndDate($leaveEndDate);
                    $leave->setCategory($fullDayCategory);
                    $leave->setNumberOfDays($leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate()));
                    $lr->addLeaf($leave);

                    $entityManager->persist($lr);
                    $this->setLRStatusSendNotificationEmail($lr, $employee, $statusApproved, $leaveRequestService);

                    // add one day to the end date of the approved request
                    $leaveForApprovalStartDate = date_add(clone $leaveEndDate, date_interval_create_from_date_string('1 day'));

                    // get the length of the leave days
                    $unpaidLeaveLength = $leaveRequestService->countLeaveDays($leaveForApprovalStartDate, $leaveForApprovalEndDate);

                    // create a new leave request instance
                    $LRForApproval = $leaveRequestService->createLRInstance($lr, $leaveRequestGroup, $employee);

                    // create a new leave instance
                    $leaveForApproval = $leaveRequestService->createLeaveInstance(
                        $leave, $LRForApproval, $unpaidCategory, $unpaidLeaveLength, $leaveForApprovalStartDate, $leaveForApprovalEndDate
                    );
                    $LRForApproval->addLeaf($leaveForApproval);

                    // details of unpaid leaves
                    $unpaidLeaveDetails['unpaidLeaveDetails'][] = array('employee' => $employee, 'unpaid' => $unpaidLeaveLength);

                    $entityManager->persist($LRForApproval);
                    $this->setLRStatusSendNotificationEmail($LRForApproval, $employee, $statusForApproval, $leaveRequestService);
                } else {
                    $unpaidLeaveLength = $leaveRequestService->countLeaveDays($leave->getStartDate(), $leave->getEndDate());

                    // set leave category to unpaid
                    $leave->setCategory($unpaidCategory);
                    $leave->setNumberOfDays($unpaidLeaveLength);
                    $lr->addLeaf($leave);

                    // details of unpaid leaves
                    $unpaidLeaveDetails['unpaidLeaveDetails'][] = array('employee' => $employee, 'unpaid' => $unpaidLeaveLength);

                    $entityManager->persist($lr);
                    $this->setLRStatusSendNotificationEmail($lr, $employee, $statusForApproval, $leaveRequestService);
                }
            } else {
                $lr->removeLeaf($leave);
                $leave = $leaveRequestService->createLeaveInstance($leave, $lr, $fullDayCategory, $countLeaveDays, $startDate, $endDate);
                $lr->addLeaf($leave);

                $entityManager->persist($lr);
                $this->setLRStatusSendNotificationEmail($lr, $employee, $statusApproved, $leaveRequestService);
            }
            $entityManager->flush();
        }
        // Sends a single email to the gm containing leave request summary, and unpaid leave details(employee, email, leave days count)
        $leaveRequestService->prepareMassLREmail($lr, $leaveRequest->getGeneralManager()->getEmail(), $unpaidLeaveDetails);

        return $unpaidLeaveDetails;
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
