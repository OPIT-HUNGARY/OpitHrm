<?php

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
            $searchRequests = $request->request->get('search');
        }
        
        $leaveRequests = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')
            ->findAllByFiltersPaginated($pagnationParameters, $searchRequests);
        
        $listingRights = $this->get('opit.model.leave_request')
            ->setLeaveRequestListingRights($leaveRequests);
        
        if ($request->request->get('resetForm') || $isSearch || null !== $offset) {
            $template = 'OpitNotesLeaveBundle:Leave:_list.html.twig';
        } else {
            $template = 'OpitNotesLeaveBundle:Leave:list.html.twig';
        }
        
        return $this->render(
            $template,
            array(
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
        $token = $securityContext->getToken();
        $leaveRequestService = $this->get('opit.model.leave_request');
        
        if ($isNewLeaveRequest) {
            $employee = $token->getUser()->getEmployee();
            $leaveRequest = new LeaveRequest();
            $leaveRequest->setEmployee($employee);
        } else {
            $leaveRequest = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->find($leaveRequestId);
            
            if (null === $leaveRequest) {
                throw $this->createNotFoundException('Missing leave request.');
            }
            
            if ($token->getUser()->getEmployee() !== $leaveRequest->getEmployee() &&
                !$this->get('security.context')->isGranted('ROLE_ADMIN') &&
                !$this->get('security.context')->isGranted('ROLE_GENERAL_MANAGER')) {
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
            new LeaveRequestType($isNewLeaveRequest),
            $leaveRequest,
            array('em' => $entityManager, 'validation_groups' => array('user'))
        );
        
        if (null !== $leaveRequest) {
            foreach ($leaveRequest->getLeaves() as $leave) {
                $children->add($leave);
            }
        }
                
        if ($request->isMethod("POST")) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                foreach ($children as $child) {
                    if (false === $leaveRequest->getLeaves()->contains($child)) {
                        $child->setLeaveRequest();
                        $entityManager->remove($child);
                    }
                }
                
                $entityManager->persist($leaveRequest);
                $entityManager->flush();
                
                if ($isNewLeaveRequest) {
                    $statusManager->changeStatus($leaveRequest, Status::CREATED, true);
                }
                
                return $this->redirect($this->generateUrl('OpitNotesLeaveBundle_leave_list'));
            }
        }
        
        return $this->render(
            'OpitNotesLeaveBundle:Leave:showLeaveRequest.html.twig',
            array_merge(
                array(
                    'form' => $form->createView(),
                    'isNewLeaveRequest' => $isNewLeaveRequest,
                    'leaveRequestStates' => $leaveRequestStates,
                    'leaveRequest' => $leaveRequest
                ),
                $isNewLeaveRequest ? array('isStatusLocked' => true, 'isEditLocked'=> false) : $leaveRequestService->setLeaveRequestAccessRights($leaveRequest, $currentStatus)
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
        $token = $securityContext->getToken();
        $entityManager = $this->getDoctrine()->getManager();
        $ids = $request->request->get('id');
        
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ($ids as $id) {
            $leaveRequest = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->find($id);

            if ($token->getUser()->getEmployee() !== $leaveRequest->getEmployee() &&
                !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedException(
                    'Access denied for leave.'
                );
            }
            $entityManager->remove($leaveRequest);
        }
        
        $entityManager->flush();
        
        return new JsonResponse('$userNames');
    }
    
    /**
     * Method to change state of leave request
     *
     * @Route("/secured/leave/state/", name="OpitNotesLeaveBundle_leave_request_state")
     * @Template()
     */
    public function changeLeaveRequestStateAction(Request $request)
    {
        $statusId = $request->request->get('statusId');
        $leaveRequestId = $request->request->get('leaveRequestId');
        $entityManager = $this->getDoctrine()->getManager();
        $leaveRequest = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')->find($leaveRequestId);
        
        return $this->get('opit.manager.leave_status_manager')
            ->changeStatus($leaveRequest, $statusId);
    }

    /**
     * To send employee leave summary
     *
     * @Route("/secured/leaves/employeesummary", name="OpitNotesLeaveBundle_leaves_employeesummary")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function employeeLeavesinfoBoardAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        // entitled leaves count
        $leaveCalculationService = $this->get('opit_notes_leave.leave_calculation_service');
        $empLeaveEntitlement = $leaveCalculationService->leaveDaysCalculationByEmployee($user->getEmployee());

        //get leave categories
        $leaveCategories = $em->getRepository('OpitNotesLeaveBundle:LeaveCategory')->findAll();

        //total leave request count
        $totalLeaveRequestCount = $em->getRepository('OpitNotesLeaveBundle:LeaveRequest')
                ->findEmployeesLRCount($user->getEmployee()->getID(), date(date('Y') . '-01' . '-01'), date(date('Y') . '-12' . '-31'));

        //finalized leave request count
        $finalizedLeaveRequestCount = $em->getRepository('OpitNotesLeaveBundle:LeaveRequest')
                ->findEmployeesLRCount($user->getEmployee()->getID(),  date(date('Y') . '-01' . '-01'), date(date('Y') . '-12' . '-31'), true);

        //pending leave request count
        $pendingLeaveRequestCount = $totalLeaveRequestCount - $finalizedLeaveRequestCount;

        //remaning leaves count
        $leftToAvail = '';

        return $this->render('OpitNotesLeaveBundle:Leave:_employeeLeavesinfoBoard.html.twig', array('empLeaveEntitlement' => $empLeaveEntitlement,
                    'leaveCategories' => $leaveCategories,
                    'pendingLeaveRequestCount' => $pendingLeaveRequestCount,
                    'finalizedLeaveRequestCount' => $finalizedLeaveRequestCount,
                    'leftToAvail' => $leftToAvail,
                    'totalLeaveRequestCount' => $totalLeaveRequestCount
        ));
    }

}
