<?php

/*
 * The MIT License
 *
 * Copyright 2014 OPIT\bota.
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
 * IMPLIED, INCLUDING BUT NOT LIMID TO THE WARRANTIES OF MERCHANTABILITY,
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\LeaveBundle\Helper\Utils;

/**
 * Description of TimeSheetController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class TimeSheetController extends Controller
{
    /**
     * To list time sheets in Notes
     *
     * @Route("/secured/timesheet/list", name="OpitNotesLeaveBundle_timesheet_list")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function listsTimeSheetAction(Request $request)
    {
        $showList = (boolean) $request->request->get('showList');
        $maxMonth = date('n');
        $availableMonths = array();

        if ($showList) {
            ++$maxMonth;
        }

        // Generate the pervious months with the numeric and name represantions.
        for ($i = --$maxMonth; $i > 0; $i--) {
            $availableMonths[$i] = date('Y F', mktime(0, 0, 0, $i, 1));
        }

        return $this->render(
            'OpitNotesLeaveBundle:TimeSheet:' . ($showList ? '_' : '') . 'listTimeSheet.html.twig',
            array('availableMonths' => $availableMonths)
        );
    }

    /**
     * To list time sheets in Notes
     *
     * @Route("/secured/timesheet/generate", name="OpitNotesLeaveBundle_timesheet_generate")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function generateTimeSheetAction(Request $request)
    {
        $page = $this->getTimeSheetPage($request);

        return $page;
    }

    /**
     * Method to export time sheet to pdf
     *
     * @Route("/secured/timesheet/export/", name="OpitNotesLeavelBundle_timesheet_export")
     * @Template()
     */
    public function exportTimeSheetToPDFAction(Request $request)
    {
        $year = $request->query->get('year');
        $month = $request->query->get('month');
        $pdfFileName = $year . '-' . $month . '_Time_Sheet_Report.pdf';
        $pdfContent = $this->getTimeSheetPage($request)->getContent();
        $pdf = $this->get('opit.manager.pdf_manager');
        $pdf->exportToPdf(
            $pdfContent,
            $pdfFileName,
            'NOTES',
            'Time Sheet',
            'Time Sheet details',
            array('leave', 'time sheet', 'notes'),
            12,
            array(),
            'L',
            'A4'
        );

        return new JsonResponse();
    }

    private function getTimeSheetPage($request)
    {
        $em = $this->getDoctrine()->getManager();
        $config = $this->container->getParameter('opit_notes_leave');
        $arrivalTime = $config['time_sheet']['arrival_time'];
        $lunchTimeInMinutes = $config['time_sheet']['lunch_time_in_minutes'];
        $divison = $config['time_sheet']['user_grouping_number'];
        $startDate = new \DateTime(date('Y-m-01'));
        $year = date('Y');
        $month = date('m');

        if (false === strrpos($arrivalTime, ':')) {
            $arrivalTime = $arrivalTime. ':00';
        }
        // If the year and month parameters are exist then set them.
        if (null !== $request->query->get('year') && null !== $request->query->get('month')) {
            $year = $request->query->get('year');
            $month = $request->query->get('month');
            $startDate->setDate($year, $month, 1);
        }
        $leaveDatesOfMonth = array();
        $leaveDates = $em->getRepository('OpitNotesLeaveBundle:LeaveDate')->findAllByYearAndMonth($year, $month);

        // Grouping the leave dates by the date.
        foreach ($leaveDates as $leaveDate) {
            $leaveDatesOfMonth[$leaveDate->getHolidayDate()->format('Y-m-d')] =
                $leaveDate->getHolidayType()->getName();
        }
        // Get the employees.
        $users = $em->getRepository('OpitNotesUserBundle:User')->findAll();
        // Grouping users into subarrays.
        $groupedUsers = Utils::groupingArrayByCounter($users, $divison);

        // Get the leave requests
        $leaveRequests = $em->getRepository('OpitNotesLeaveBundle:LeaveRequest')->findLeaveRequestsByDates(
            date($year.'-'.$month.'-01'),
            date($year.'-'.$month.'-31')
        );
        $leaveDays = array();

        // Fetch leaves for every leave day.
        foreach ($leaveRequests as $leaveRequest) {
            foreach ($leaveRequest->getLeaves() as $leave) {
                $days = Utils::getDaysBetweenTwoDateTime($leave->getStartDate(), $leave->getEndDate());
                // Fetch leave days by employee id and category name
                foreach ($days as $day) {
                    $leaveDays[$day->format('Y-m-d')][$leaveRequest->getEmployee()->getId()] =
                        $leave->getCategory()->getName();
                }
            }
        }
        // Get the days of the actual month.
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval("P1M"));
        $daysOfMonth = Utils::getDaysBetweenTwoDateTime($startDate, $endDate);

        return $this->render(
            'OpitNotesLeaveBundle:TimeSheet:showTimeSheet.html.twig',
            array(
                'groupedUsers' => $groupedUsers,
                'daysOfMonth' => $daysOfMonth,
                'leaveDatesOfMonth' => $leaveDatesOfMonth,
                'leaveDays' => $leaveDays,
                'arrivalTime' => $arrivalTime,
                'lunchTimeInMinutes' => $lunchTimeInMinutes,
                'division' => $divison,
                'year' => $year,
                'month' => $month
            )
        );
    }
}
