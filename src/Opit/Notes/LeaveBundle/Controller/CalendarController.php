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
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\Notes\UserBundle\Entity\Employee;
use Opit\Notes\StatusBundle\Entity\Status;

/**
 * Description of CalendarController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
class CalendarController extends Controller
{

    /**
     * Show calendar for team
     *
     * @Route("/secured/calendar/team", name="OpitNotesLeaveBundle_calendar_team")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function showTeamLeavesCalendarAction($partial = false)
    {
        $securityContext = $this->container->get('security.context');
        $employees = $this->getTeamsEmployees($securityContext->getToken()->getUser()->getEmployee());

        if (!$partial) {
            return $this->render('OpitNotesLeaveBundle:Calendar:teamLeavesCalendar.html.twig', array('employees' => $employees));
        } else {
            return $this->render('OpitNotesLeaveBundle:Calendar:_teamLeavesCalendar.html.twig', array('employees' => $employees));
        }
    }

    /**
     * Get team employee leaves
     *
     * @Route("/secured/calendar/team/employees", name="OpitNotesLeaveBundle_calendar_team_employees")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function showTeamEmployeeLeavesAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $securityContext = $this->container->get('security.context');
        $employee = $securityContext->getToken()->getUser()->getEmployee();

        $teamsEmployees = $this->getTeamsEmployees($employee);

        // get all approved leave requests employees are in
        $leaveRequests = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')
            ->findEmployeesLeaveRequests(
                $teamsEmployees,
                date('Y-m-d', $request->query->get('start')),
                date('Y-m-d', $request->query->get('end')),
                Status::APPROVED
            );

        $leaves = array();

        // loop through all leave requests
        foreach ($leaveRequests as $leaveRequest) {
            // loop hrough all leave request leaves
            $employee = $leaveRequest->getEmployee();
            foreach ($leaveRequest->getLeaves() as $leave) {
                // set leave data
                $leaves[] = array(
                    'title' => strtoupper($employee->getEmployeeName()) . ' - ' . $leave->getCategory()->getName(),
                    'start' => $leave->getStartDate()->format('Y-m-d'),
                    'end' => $leave->getEndDate()->format('Y-m-d'),
                    'className' => str_replace(' ', '_', ($employee->getEmployeeName() . '-' . $employee->getId())),
                    'textColor' => 'white'
                );
            }
        }

        return new JsonResponse($leaves);
    }

    /**
     *
     * @param type $employee
     * @return type
     */
    protected function getTeamsEmployees(Employee $employee)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $securityContext = $this->container->get('security.context');
        $teamsEmployees = array();

        if ($securityContext->isGranted('ROLE_ADMIN')) {
            $teamsEmployees = $entityManager->getRepository('OpitNotesUserBundle:Employee')->findAll();
        } else {
            // if employee is not part of any team get his data only
            $teamsEmployees = $entityManager->getRepository('OpitNotesUserBundle:Employee')->findTeamEmployees($employee->getId());
        }

        return $teamsEmployees;
    }
}
