<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\OpitHrm\UserBundle\Entity\Employee;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\Component\ICalendar as ICal;
use Opit\Component\Utils\Utils;

/**
 * Description of CalendarController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class CalendarController extends Controller
{
    /**
     * Show calendar for team
     *
     * @Route("/secured/calendar/team", name="OpitOpitHrmLeaveBundle_calendar_team")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function showTeamLeavesCalendarAction($partial = false)
    {
        $securityContext = $this->container->get('security.context');
        $employees = $this->getTeamsEmployees($securityContext->getToken()->getUser()->getEmployee());

        if (!$partial) {
            return $this->render(
                'OpitOpitHrmLeaveBundle:Calendar:teamLeavesCalendar.html.twig',
                array('employees' => $employees)
            );
        } else {
            return $this->render(
                'OpitOpitHrmLeaveBundle:Calendar:_teamLeavesCalendar.html.twig',
                array('employees' => $employees)
            );
        }
    }

    /**
     * Show calendar for admin
     *
     * @Route("/secured/calendar/team/admin", name="OpitOpitHrmLeaveBundle_calendar_team_admin")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function showTeamLeavesAdminCalendarAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $employees = $entityManager->getRepository('OpitOpitHrmUserBundle:Employee')->findAll();

        return $this->render(
            'OpitOpitHrmLeaveBundle:Calendar:teamLeavesCalendar.html.twig',
            array('employees' => $employees)
        );
    }

    /**
     * Get team employee leaves
     *
     * @Route("/secured/calendar/team/employees", name="OpitOpitHrmLeaveBundle_calendar_team_employees")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function showTeamEmployeeLeavesAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $securityContext = $this->container->get('security.context');
        $employee = $securityContext->getToken()->getUser()->getEmployee();

        $teamsEmployees = $this->getTeamsEmployees($employee);
        $startDate = date('Y-m-d', $request->query->get('start'));
        $endDate = date('Y-m-d', $request->query->get('end'));

        // get all approved leave requests employees are in
        $leaveRequests = $entityManager->getRepository('OpitOpitHrmLeaveBundle:LeaveRequest')
            ->findEmployeesLeaveRequests($teamsEmployees, $startDate, $endDate, Status::APPROVED);

        $leaveWorkingDays = $entityManager->getRepository('OpitOpitHrmLeaveBundle:LeaveDate')
            ->findLWDInRange($startDate, $endDate);

        $leaves = array();
        $leaveWorkingDaysArray = array();

        // Loop through all leave working days and add it to the calendar
        foreach ($leaveWorkingDays as $leaveWorkingDay) {
            $lwdDate = $leaveWorkingDay->getHolidayDate()->format('Y-m-d');
            $leaveWorkingDaysArray[] = array(
                'title' => $leaveWorkingDay->getHolidayType()->getName(),
                'start' => $lwdDate,
                'end' => $lwdDate,
                'textColor' => 'white',
                'className' => 'border-none background-color-default-red lwd_' . $lwdDate
            );
        }
        // loop through all leave requests
        foreach ($leaveRequests as $leaveRequest) {
            // loop through all leave request leaves
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

        return new JsonResponse(array_merge($leaves, $leaveWorkingDaysArray));
    }

    /**
     * Exports team employee leaves in iCalendar format
     *
     * @Route("/secured/calendar/team/leaves/export", name="OpitOpitHrmLeaveBundle_calendar_team_leaves_export")
     * @Method({"POST"})
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function exportTeamEmployeeLeavesAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $securityContext = $this->container->get('security.context');
        $employee = $securityContext->getToken()->getUser()->getEmployee();
        $statusManager = $this->get('opit.manager.leave_status_manager');

        $teamsEmployees = $this->getTeamsEmployees($employee);
        // get all approved leave requests employees are in
        $leaveRequests = $entityManager->getRepository('OpitOpitHrmLeaveBundle:LeaveRequest')
            ->findEmployeesLeaveRequests(
                $teamsEmployees,
                $request->request->get('start'),
                $request->request->get('end'),
                Status::APPROVED
            );

        $iCal = new ICal\ICalendar();

        // loop through all leave requests
        foreach ($leaveRequests as $leaveRequest) {
            // loop through all leave request leaves
            $employee = $leaveRequest->getEmployee();
            $currentStatus = $statusManager->getCurrentStatusMetaData($leaveRequest);
            foreach ($leaveRequest->getLeaves() as $leave) {
                $iCalEvent = new ICal\ICalendarEvent();
                $iCalEvent->setDtStamp($currentStatus->getCreated());
                $iCalEvent->setSummary(
                    ucwords($employee->getEmployeeName()) . ' - ' . $leave->getCategory()->getName()
                );
                $iCalEvent->setDtStart($leave->getStartDate());
                $iCalEvent->setDtEnd($leave->getEndDate());
                $iCalEvent->addCategory($leave->getCategory()->getName());

                $iCal->addEvent($iCalEvent);
            }
        }

        $iCalContent = $iCal->render();
        $filename = Utils::sanitizeString(
            $this->container->getParameter('application_name') . '-' .$request->request->get('title')
        ) . '.ics';

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'text/calendar');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '";');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-length', strlen(utf8_decode($iCalContent)));
        $response->sendHeaders();
        $response->setContent($iCalContent);

        return $response;
    }

    /**
     *
     * @param type $employee
     * @return type
     */
    protected function getTeamsEmployees(Employee $employee)
    {
        $entityManager = $this->getDoctrine()->getManager();
        // if employee is not part of any team get his data only
        $teamsEmployees = $entityManager->getRepository('OpitOpitHrmUserBundle:Employee')
            ->findTeamEmployees($employee->getId());

        return $teamsEmployees;
    }
}
