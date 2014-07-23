<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Service;

use Opit\OpitHrm\LeaveBundle\Model\LeaveEntitlementEmployeeInterface;
use Doctrine\ORM\EntityManager;

/**
 * Leave Calculation Service.
 * This calculation service is based on the Hungarian Law in 2014.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveCalculationService
{
    /**
     * em
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * service container
     *
     * @var object
     */
    protected $options;

    /**
     * Day of the Calendar
     *
     * @var integer
     */
    protected $calendarDays;

    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManager
     */
    public function __construct(EntityManager $entityManager, $options)
    {
        $this->em = $entityManager;
        $this->options = $options;

        // Set default days if not passed by $options
        $this->calendarDays = array_key_exists('calendar_days', $this->options) ? $this->options['calendar_days'] : 365;
    }

    /**
     * Calculating leave days by the employee's data
     *
     * @param \Opit\OpitHrm\HolidayBundle\Model\LeaveEntitlementEmployeeInterface $employee
     * @return integer the number of leaves in the current year.
     */
    public function leaveDaysCalculationByEmployee(LeaveEntitlementEmployeeInterface $employee)
    {
        // Get the number of leaves after the employee how old will be in this year.
        $leaveDaysbyAge = $this->em->getRepository('OpitOpitHrmLeaveBundle:LeaveSetting')->getNumberOfLeavesByAge(
            $this->calculateAgeFromDateOfBirth($employee->getDateOfBirth())
        );
        // Get the remaining days if the employee joined in this year.
        $remainingDays = $this->calculateRemainingDays($employee->getJoiningDate());
        $numberOfChildren = $this->em->getRepository('OpitOpitHrmLeaveBundle:LeaveSetting')->getNumberOfLeavesByChildren(
            $employee->getNumberOfChildren()
        );

        $result = $leaveDaysbyAge;

        // If the user joined to the company at this year then the reamining days value will be added to the calculation.
        if (null !== $remainingDays) {
            // Based on the / and * parity
            $result *= $remainingDays / $this->calendarDays;
        }
        // Add the number of leaves by the number of children.
        $result += $numberOfChildren;

        // Return by the rounded value
        return (int) round($result);
    }

    /**
     * Calculating age from date of birth of employee
     *
     * @param \DateTime $dateOfBirth
     * @return integer age of the employee
     */
    private function calculateAgeFromDateOfBirth(\DateTime $dateOfBirth)
    {
        $endOfThisYear = new \DateTime(date('Y-12-31'));

        return $dateOfBirth->diff($endOfThisYear)->y;
    }

    /**
     * Calculating remaining days from the joining date of employee
     *
     * @param \DateTime $joiningDate
     * @return integer day of remaining days
     */
    private function calculateRemainingDays(\DateTime $joiningDate)
    {
        $startOfThisYear = new \DateTime(date('Y-01-01'));

        // If the employee joined to the company the last year then the remaining days will not be needed.
        if ( $joiningDate->format('Y') < $startOfThisYear->format('Y')) {
            return null;
        } else {
            // remainig days from this year.
            return $this->calendarDays - $joiningDate->diff($startOfThisYear)->days;
        }
    }
}
