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
 * Description of ICalendarEventInterface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage Component
 */
interface ICalendarEventInterface
{
    const DELIMITER = "\r\n";

    /**
     * Main rendering method generating valid VEVENT output
     */
    public function render();

    /**
     * Returns the event's uid
     */
    public function getUID();

    /**
     * Sets the unique identifier for an event
     * The "UID" itself MUST be a globally unique identifier.
     * Using a DATE-TIME value on the left-hand side and a domain name or
     * domain literal on the right-hand side makes it possible to guarantee
     * uniqueness since no two hosts should be using the same domain name
     * or IP address at the same time.
     *
     * @param string $uid
     */
    public function setUID($uid);

    /**
     * Returns the event's date-time stamp in UTC time format
     */
    public function getDtStamp();

    /**
     * Sets the event's date-time stamp
     * The value MUST be specified in the UTC time format.
     *
     * Examples:
     * DTSTART:19970714T173000Z; UTC time
     *
     * @param \DateTime $datetime
     */
    public function setDtStamp(\DateTime $datetime);

    /**
     * Returns the event's start date-time
     */
    public function getDtStart();

    /**
     * Sets the event's start date-time
     * To properly communicate a fixed time in a property value, either
     * UTC time or local time with time zone reference MUST be specified.
     *
     * Examples:
     * DTSTART:19970714T133000; Local time
     * DTSTART:19970714T173000Z; UTC time
     * DTSTART;TZID=America/New_York:19970714T133000; Local time and time zone reference
     *
     * @param \DateTime $datetime
     */
    public function setDtStart(\DateTime $datetime);

    /**
     * Returns the event's start date-time
     */
    public function getDtEnd();

    /**
     * Sets the event's start date-time
     * To properly communicate a fixed time in a property value, either
     * UTC time or local time with time zone reference MUST be specified.
     *
     * Examples:
     * DTSTART:19970714T133000; Local time
     * DTSTART:19970714T173000Z; UTC time
     * DTSTART;TZID=America/New_York:19970714T133000; Local time and time zone reference
     *
     * @param \DateTime $datetime
     */
    public function setDtEnd(\DateTime $datetime);

    /**
     * Returns the event's short summary or subject
     * This property is used to capture a short, one-line summary about the activity.
     */
    public function getSummary();

    /**
     * Sets the event's short summary or subject
     * @param string $text
     */
    public function setSummary($text);
}
