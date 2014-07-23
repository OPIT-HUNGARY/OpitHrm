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
 * ICalendar class
 *
 * This class is intended to return RFC5545 compliant VCALENDAR output.
 * The ICalendar is the main class implementing ICalendarEvent objects.
 * Any other extension defined by the RFC may be included later.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage Component
 * @version 1.0
 */
class ICalendar implements \Opit\Component\ICalendar\ICalendarInterface
{
    const VERSION = '1.0';

    protected $version; // iCalendar version
    protected $prodid; // iCalendar product identifier
    protected $calscale; // iCalendar scale  used for the calendar information
    protected $output; // The ready iCalendar output

    protected $events;


    /**
     * Class constructor
     */
    public function __construct($calscale = 'GREGORIAN')
    {
        $this->version = '2.0';
        $this->prodid = '-//OPIT Consulting Kft//NONSGML ICalendar' . self::VERSION . '//EN';
        $this->calscale = $calscale;

        $this->events = array();
    }

    /**
     * Renders a VCALENDAR object and its related childs
     *
     * {@inheritdoc}
     */
    public function render()
    {
        // Begin iCalendar
        $this->output = 'BEGIN:VCALENDAR' . self::DELIMITER;
        $this->output .= 'VERSION:' . $this->version . self::DELIMITER;
        $this->output .= 'PRODID:' . $this->prodid . self::DELIMITER;
        $this->output .= 'CALSCALE:' . $this->calscale . self::DELIMITER;

        // Iterate over events and render content
        foreach ($this->getEvents() as $event) {
            $this->output .= $event->render();
        }

        // End iCalendar
        $this->output .= 'END:VCALENDAR' . self::DELIMITER;

        return $this->output;
    }

    /**
     * {@inheritdoc}
     * @return \Opit\Component\ICalendar\ICalendar
     */
    public function addEvent(ICalendarEventInterface $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return boolean
     */
    public function removeEvent(ICalendarEventInterface $event)
    {
        $key = array_search($event, $this->events, true);

        if ($key !== false) {
            unset($this->events[$key]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return $this->events;
    }
}
