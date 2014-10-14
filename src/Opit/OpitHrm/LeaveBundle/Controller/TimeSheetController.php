<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Component\Utils\Utils;
use Opit\OpitHrm\LeaveBundle\Entity\LogTimesheet;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

/**
 * Description of TimeSheetController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class TimeSheetController extends Controller
{
    /**
     * To list time sheets in OPIT-HRM
     *
     * @Route("/secured/timesheet/lists", name="OpitOpitHrmLeaveBundle_timesheet_list")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listsTimeSheetAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $orderParams = $request->request->get('order');
        $currentMonth = date('n');
        $availableMonths = array();
        $logTimesheets = array();

        // Generate the pervious months with the numeric and name represantions.
        for ($i = $currentMonth; $i > 0; $i--) {
            $availableMonths[$i] = new \DateTime(date('Y-m', mktime(0, 0, 0, $i, 1)));

            // Get the leave data
            $leaveData = $this->getLeaveData(date('Y'), $i);
            $leaveIds = $leaveData['leaveIds'];

            // Generate hash id.
            $hashId = $this->generateHashIdForData($leaveIds, 'json');

            $logTimesheetList = $em->getRepository('OpitOpitHrmLeaveBundle:LogTimesheet')->findBy(
                array('timesheetDate' => new \DateTime(date('Y-'.$i.'-01'))),
                array('id' => 'ASC')
            );

            foreach ($logTimesheetList as $logTimesheet) {

                if (!isset($logTimesheets[$logTimesheet->getTimesheetDate()->format('Y-m-d')][$logTimesheet->getAction()])) {
                    $logTimesheets[$logTimesheet->getTimesheetDate()->format('Y-m-d')][$logTimesheet->getAction()] = 1;
                } else {
                    $logTimesheets[$logTimesheet->getTimesheetDate()->format('Y-m-d')][$logTimesheet->getAction()]++;
                }
                // Compare the hash ids.
                if (LogTimesheet::EMAILED === $logTimesheet->getAction()) {
                    $logTimesheets[$logTimesheet->getTimesheetDate()->format('Y-m-d')]['inSync'] =
                        ($hashId == $logTimesheet->getHashId());
                }
            }
        }
        // Ordering the month. If the direction is desc then reverse the $availableMonths array.
        if ($showList && ('desc' === $orderParams['dir']) && 'month' === $orderParams['field']) {
            $availableMonths = array_reverse($availableMonths);
        }

        return $this->render(
            'OpitOpitHrmLeaveBundle:TimeSheet:' . ($showList ? '_' : '') . 'listTimeSheet.html.twig',
            array('availableMonths' => $availableMonths, 'logTimesheets' => $logTimesheets)
        );
    }

    /**
     * To list time sheets in OPIT-HRM
     *
     * @Route("/secured/timesheet/generate/{token}", name="OpitOpitHrmLeaveBundle_timesheet_generate")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function generateTimeSheetAction($token)
    {
        $page = $this->getTimeSheetPage(LogTimesheet::PRINTED, $token);

        return $page;
    }

    /**
     * To list time sheets in OPIT-HRM
     *
     * @Route("/customer/timesheet/generate/{token}", name="OpitOpitHrmLeaveBundle_customer_timesheet_generate")
     * @Template()
     */
    public function generateTimeSheetForCustomerAction($token)
    {
        $page = $this->getTimeSheetPage(LogTimesheet::PRINTED, $token);

        return $page;
    }

    /**
     * Method to export time sheet to pdf
     *
     * @Route("/secured/timesheet/export/{token}", name="OpitOpitHrmLeavelBundle_timesheet_export")
     * @Template()
     */
    public function exportTimeSheetToPDFAction($token)
    {
        // Downloading the generated pdf.
        $this->generateTimesheetPDF($token, 'D');

        return new JsonResponse();
    }

    /**
     * To list time sheets in OPIT-HRM
     *
     * @Route("/secured/timesheet/sendmail/{token}", name="OpitOpitHrmLeaveBundle_timesheet_sendmail")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function sendEmailAction($token)
    {
        $applicationName = $this->container->getParameter('application_name');
        $requestQuery = base64_decode($token);
        $tokenArray = explode('|', $requestQuery);
        $dateArray = array('year' => $tokenArray[0], 'month' => $tokenArray[1]);
        $year = $dateArray['year'];
        $month = $dateArray['month'];
        $mailer = $this->get('opit.component.email_manager');
        $em = $this->getDoctrine()->getManager();
        $dateTime = new \DateTime();
        $dateTime->setDate($year, $month, 1);
        $action = LogTimesheet::EMAILED;

        $url = $this->generateUrl(
            'OpitOpitHrmLeaveBundle_customer_timesheet_generate',
            array('token' => base64_encode($year.'|'.$month)),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Get the payrolls.
        $payrolls = $em->getRepository('OpitOpitHrmUserBundle:Groups')->findOneByRole('ROLE_PAYROLL');
        $payrollAddresses = array();
        // Get the email addresses of payrolls.
        foreach ($payrolls->getUsers() as $user) {
            $payrollAddresses[] = $user->getEmail();
        }
        // Prepare and send email to payroll(s).
        $mailer->setRecipient($payrollAddresses);
        $mailer->setSubject(
            '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - ' . $year . '-' . $month . ' timesheet is available'
        );

        $mailer->setBodyByTemplate(
            'OpitOpitHrmLeaveBundle:Mail:timesheet.html.twig',
            array('url' => $url, 'dateTime' => $dateTime)
        );

        // Add attachment, the PDF is generated at runtime.
        $mailer->addAttachment(
            // S parameter means returning the PDF file content as a string in the generateTimesheetPDF method.
            array(
                'content' => $this->generateTimesheetPDF($token, 'S'),
                'filename' => $year . '-' . $month . '_Time_Sheet_Report.pdf',
                'type' => 'application/pdf'
            ),
            true
        );
        $mailer->sendMail();

        // For the serialization.
        $leaveData = $this->getLeaveData($year, $month);
        $leaveIds = $leaveData['leaveIds'];

        $this->syncLeaves($leaveIds, $year, $month, $action);

        // Redirect to the list page.
        return $this->redirect($this->generateUrl('OpitOpitHrmLeaveBundle_timesheet_list'));
    }

    /**
     * Get the timesheet page fly on mode.
     * The generated timesheets are not saved.
     *
     * @param string $action the action's name.
     * @param string $token query parameters.
     * @return resource the html page.
     */
    private function getTimeSheetPage($action, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $config = $this->container->getParameter('opit_opit_hrm_leave');
        $arrivalTime = $config['time_sheet']['arrival_time'];
        $lunchTimeInMinutes = $config['time_sheet']['lunch_time_in_minutes'];
        $divison = $config['time_sheet']['user_grouping_number'];
        $startDate = new \DateTime(date('Y-m-01'));
        $year = date('Y');
        $month = date('m');

        if (false === strrpos($arrivalTime, ':')) {
            $arrivalTime = $arrivalTime. ':00';
        }

        $requestQuery = base64_decode($token);
        $tokenArray = explode('|', $requestQuery);
        $dateArray = array('year' => $tokenArray[0], 'month' => $tokenArray[1]);

        // If the year and month parameters are exist then set them.
        if (isset($dateArray['year']) && isset($dateArray['month'])) {
            $year = $dateArray['year'];
            $month = $dateArray['month'];
            $startDate->setDate($year, $month, 1);
        }
        $leaveDatesOfMonth = array();
        $leaveDates = $em->getRepository('OpitOpitHrmLeaveBundle:LeaveDate')->findAllFiltered(
            array('year' => array($year), 'month' => array($month))
        );

        // Grouping the leave dates by the date.
        foreach ($leaveDates as $leaveDate) {
            $leaveDatesOfMonth[$leaveDate->getHolidayDate()->format('Y-m-d')] = $leaveDate->getHolidayType();
        }

        // Get the role hierarchy.
        $hierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        // Get all roles which are above the user role in the role hierarchy as time sheet only requires role user and above
        $higherRoles = Utils::getHigherLevelRoles($hierarchy);

        // Get the leave data
        $leaveData = $this->getLeaveData($year, $month);
        $leaveIds = $leaveData['leaveIds'];
        $leaveDays = $leaveData['leaveDays'];

        $this->syncLeaves($leaveIds, $year, $month, $action);

        // Get the days of the actual month.
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        $daysOfMonth = Utils::diffDays($startDate, $endDate);

        // Get the employees by the roles and employement.
        $users = $em->getRepository('OpitOpitHrmUserBundle:User')->findEmployedUsers(
            '',
            new \DateTime($dateArray['year'] . '-' . $dateArray['month']),
            new \DateTime($dateArray['year'] . '-' . $dateArray['month'] . '-' . count($daysOfMonth)),
            $higherRoles
        );

        // Grouping users into subarrays.
        $groupedUsers = Utils::groupingArrayByCounter($users, $divison);

        return $this->render(
            'OpitOpitHrmLeaveBundle:TimeSheet:showTimeSheet.html.twig',
            array(
                'action' => $action,
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

    /**
     * Generate timesheet pdf
     *
     * @param string $token query parameters.
     * @param string $outputType
     * @return string the pdf file.
     */
    private function generateTimesheetPDF($token, $outputType)
    {
        $requestQuery = base64_decode($token);
        $tokenArray = explode('|', $requestQuery);
        $dateArray = array('year' => $tokenArray[0], 'month' => $tokenArray[1]);

        $pdfFileName = $dateArray['year'] . '-' . $dateArray['month'] . '_Time_Sheet_Report.pdf';
        $pdfContent = $this->getTimeSheetPage(LogTimesheet::DOWNLOADED, $token)->getContent();
        $pdf = $this->get('opit.manager.pdf_manager');
        $pdfFile = $pdf->exportToPdf(
            $pdfContent,
            $pdfFileName,
            'OPIT-HRM',
            'Time Sheet',
            'Time Sheet details',
            array('leave', 'time sheet', 'opithrm'),
            12,
            array(),
            'L',
            'A4',
            $outputType
        );

        return $pdfFile;
    }

    /**
     * Sync the leaves by the leave id.
     * Generate hash id, and refresh the log for timesheets.
     *
     * @param array $leaveIds the ids of leaves.
     * @param integer $year year
     * @param integer $month month
     * @param string $action action's name
     * @return boolean
     */
    private function syncLeaves($leaveIds, $year, $month, $action = null)
    {
        $em = $this->getDoctrine()->getManager();

        // Generate hash id.
        $hashId = $this->generateHashIdForData($leaveIds, 'json');

        // Get the log of current timesheet.
        $logTimeSheet = $em->getRepository('OpitOpitHrmLeaveBundle:LogTimesheet')->findOneBy(array(
            'timesheetDate' => new \DateTime(date($year.'-'.$month.'-01')),
            'action' => $action
        ));
        // Update the log for current timesheet.
        $result = $this->updateLogTimesheet($logTimeSheet, $hashId, array('year' => $year, 'month' => $month), $action);

        return $result;
    }

    /**
     * Get the leave days and leave ids.
     *
     * @param integer $year year
     * @param integer $month month
     * @return array of leave data
     */
    private function getLeaveData($year, $month)
    {
        $em = $this->getDoctrine()->getManager();

        $leaveData['leaveDays'] = array();
        $leaveData['leaveIds'] = array();
        // Get the leave requests
        $leaveRequests = $em->getRepository('OpitOpitHrmLeaveBundle:LeaveRequest')->findApprovedLeaveRequestsByDates(
            date($year.'-'.$month.'-01'),
            date($year.'-'.$month.'-31')
        );
        // Fetch leaves for every leave day.
        foreach ($leaveRequests as $leaveRequest) {
            foreach ($leaveRequest->getLeaves() as $leave) {
                // Fetch leave ids to the serialization.
                $leaveData['leaveIds'][$leaveRequest->getId()][] = $leave->getId();

                $days = Utils::diffDays($leave->getStartDate(), $leave->getEndDate());
                // Fetch leave days by employee id and category name
                foreach ($days as $day) {
                    $leaveData['leaveDays'][$day->format('Y-m-d')][$leaveRequest->getEmployee()->getId()] =
                        $leave->getCategory()->getName();
                }
            }
        }

        return $leaveData;
    }

    /**
     * Update the log for timesheet
     * If the hash ids are different then update it for the current timesheet.
     * If the timesheet log does not exist then create one for it.
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LogTimesheet $logTimeSheet
     * @param string $hashId
     * @return boolean
     */
    private function updateLogTimesheet($logTimeSheet, $hashId, $dates, $action)
    {
        $em = $this->getDoctrine()->getManager();

        // Create new logTimesheet object.
        $logTimeSheet = new LogTimesheet();
        $logTimeSheet->setHashId($hashId);
        $logTimeSheet->setTimesheetDate(new \DateTime(date($dates['year'].'-'.$dates['month'].'-01')));
        $logTimeSheet->setAction($action);

        $em->persist($logTimeSheet);
        $em->flush();

        // If the hash ids are different then return true, else false.
        return true;
    }

    /**
     * Generating hash id for the given data.
     *
     * @param mixed $data for generating the hash id.
     * @param string $format the type of the encoding. The json and xml format is avaliable.
     * @return string generated hash id
     */
    private function generateHashIdForData($data, $format)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        // Create serializer object.
        $serializer = new Serializer($normalizers, $encoders);

        // Return the generated hash id.
        return md5($serializer->serialize($data, $format));
    }
}
