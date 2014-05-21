<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Helper;

/**
 * The Utils class is a helper class for all class in the project.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class Utils
{
    /**
     * Get all dates between two datetime.
     *
     * @param \DateTime $sDate the start date
     * @param \DateTime $eDate the end date
     * @return array of datetimes
     */
    public static function getDaysBetweenTwoDateTime($sDate, $eDate)
    {
        $startDate = clone $sDate;
        $endDate = clone $eDate;
        $days = array();

        // Collect the days of month.
        while($startDate->getTimestamp() < $endDate->getTimestamp()) {
            $days[] = clone $startDate;
            $startDate->add(new \DateInterval("P1D"));
        }

        return $days;
    }

    /**
     * Grouping an array collection by a counter
     *
     * @param array $collection
     * @param integer $division
     * @return array the grouped collection array
     */
    public static function groupingArrayByCounter($collection, $division)
    {
        $result = array();
        $counter = 0;
        $index = 0;

        // Grouping collection by counter
        // The elements of collection will be ordered into subarrays by the division number.
        foreach ($collection as $data) {

            if ($counter % $division == 0) {
                ++$index;
            }
            $result[$index][] = $data;
            $counter++;
        }

        return $result;
    }
}
