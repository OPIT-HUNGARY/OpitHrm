<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Component\ICalendar;

/**
 * Description of ICalendarInterface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage Component
 */
interface ICalendarInterface
{
    const DELIMITER = "\r\n";

    /**
     * Main rendering method generating valid VCALENDAR output
     */
    public function render();

    /**
     * Adds an ical event
     *
     * @param \Opit\Component\ICalendar\ICalendarEventInterface $event
     */
    public function addEvent(ICalendarEventInterface $event);

    /**
     * Removes an ical event
     *
     * @param \Opit\Component\ICalendar\ICalendarEventInterface $event
     */
    public function removeEvent(ICalendarEventInterface $event);

    /**
     * Gets an array of ical events
     */
    public function getEvents();
}
