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
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Opit\Notes\LeaveBundle\Service;

use Opit\Notes\LeaveBundle\Model\LeaveEntitlementEmployeeInterface;
use Doctrine\ORM\EntityManager;

/**
 * Leave Calculation Service.
 * This calculation service is based on the Hungarian Law in 2014.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage HolidayBundle
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
     * @param \Opit\Notes\HolidayBundle\Model\LeaveEntitlementEmployeeInterface $employee
     * @return integer the number of leaves in the current year.
     */
    public function leaveDaysCalculationByEmployee(LeaveEntitlementEmployeeInterface $employee)
    {
        // Get the number of leaves after the employee how old will be in this year.
        $leaveDaysbyAge = $this->em->getRepository('OpitNotesLeaveBundle:LeaveSetting')->getNumberOfLeavesByAge(
            $this->calculateAgeFromDateOfBirth($employee->getDateOfBirth())
        );
        // Get the remaining days if the employee joined in this year.
        $remainingDays = $this->calculateRemainingDays($employee->getJoiningDate());
        $numberOfChildren = $this->em->getRepository('OpitNotesLeaveBundle:LeaveSetting')->getNumberOfLeavesByChildren(
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
