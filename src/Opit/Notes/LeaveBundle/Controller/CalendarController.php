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

namespace Opit\Notes\LeaveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    public function showTeamLeavesCalendarAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $securityContext = $this->container->get('security.context');

        if ($securityContext->isGranted('ROLE_ADMIN')) {
            $teamsEmployees = $entityManager->getRepository('OpitNotesUserBundle:Employee')->findAllEmployeeIdNameHydrated();
        } else {
            $employee = $securityContext->getToken()->getUser()->getEmployee();
            
            if (count($employee->getTeams()) > 0) {
                $teamsEmployees = $this->getRelatedEmployeesFromTeams($employee->getId());
            } else {
                // if employee is not part of any team get his data only
                $teamsEmployees = $entityManager->getRepository('OpitNotesUserBundle:Employee')->findEmployeeIdNameHydrated($employee->getId());
            }
        }
        
        $employees = array();
        
        // loop through all teams
        foreach ($teamsEmployees as $employee) {
            // check if employee has been already added
            if (!array_key_exists($employee['id'], $employees)) {
                // set employee properties
                $employeeId = $employee['id'];
                $employeeName = $employee['employeeName'];
                $employees[$employeeId] = array(
                    'name' => strtoupper($employee['employeeName']),
                    'class' => $employeeName . '-' . $employeeId
                );
            }
        }
        
        return $this->render('OpitNotesLeaveBundle:Calendar:teamLeavesCalendar.html.twig', array('employees' => $employees));
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

        if ($securityContext->isGranted('ROLE_ADMIN')) {
            $teamsEmployees = $entityManager->getRepository('OpitNotesUserBundle:Employee')->findAllEmployeeIdNameHydrated();
        } else {
            if (count($employee->getTeams()) > 0) {
                $teamsEmployees = $this->getRelatedEmployeesFromTeams($employee->getId());
            } else {
                // if employee is not part of any team get his data only
                $teamsEmployees = $entityManager->getRepository('OpitNotesUserBundle:Employee')->findEmployeeIdNameHydrated($employee->getId());
            }
        }
        
        // get all leave requests employees are in
        $leaveRequests = $entityManager->getRepository('OpitNotesLeaveBundle:LeaveRequest')
            ->findEmployeesLeaveRequests(
                $teamsEmployees,
                date('Y-m-d', $request->query->get('start')),
                date('Y-m-d', $request->query->get('end'))
            );
        
        $leaves = array();
        
        // loop through all leave requests
        foreach ($leaveRequests as $leaveRequest) {
            // loop hrough all leave request leaves
            foreach ($leaveRequest['leaves'] as $leave) {
                // set leave data
                $employee = $leaveRequest['employee'];
                $leaves[] = array(
                    'title' => strtoupper($employee['employeeName']) . ' - ' . $leave['description'],
                    'start' => $leave['startDate']->format('Y-m-d'),
                    'end' => $leave['endDate']->format('Y-m-d'),
                    'className' => $employee['employeeName'] . '-' . $employee['id'],
                    'textColor' => 'white'
                );
            }
        }
        
        return new JsonResponse($leaves);
    }
    
    protected function getRelatedEmployeesFromTeams($employeeId)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        // get all teams employee is part of
        $employeeTeams = $entityManager->getRepository('OpitNotesUserBundle:Employee')
            ->findEmployeeTeamIds($employeeId);

        // get all employees in teams
        $teamsEmployees = $entityManager->getRepository('OpitNotesUserBundle:Team')
            ->findTeamsEmployees($employeeTeams);
        
        return $teamsEmployees;
    }
}
