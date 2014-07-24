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
 * ICalendarEvent class
 *
 * This class is intended to return RFC5545 compliant VEVENT output.
 * It serves as a child class for the main ICalendar class.
 * Any other features defined by the RFC may be included later.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage Component
 * @version 1.0
 */
class ICalendarEvent implements ICalendarEventInterface
{
    protected $uid;
    protected $dtStamp;
    protected $dtStart;
    protected $dtEnd;
    protected $summary;
    protected $description;
    protected $location;
    protected $categories;

    public function __construct($uid = null)
    {
        if (null === $uid) {
            $uid = $this->generateUID();
        }

        $this->uid = $uid;
        $this->categories = array();
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // Begin iCalendar event
        $this->output = 'BEGIN:VEVENT' . self::DELIMITER;
        $this->output .= 'UID:' . $this->getUID() . self::DELIMITER;
        $this->output .= 'DTSTAMP:' . $this->getDtStamp() . self::DELIMITER;
        $this->output .= 'SUMMARY:' . $this->getSummary() . self::DELIMITER;
        // Colon for DTSTART and DTEND is added by the setter functions
        $this->output .= 'DTSTART' . $this->getDtStart() . self::DELIMITER; // Colon ":" added by setter
        $this->output .= 'DTEND' . $this->getDtEnd() . self::DELIMITER; // Colon ":" added by setter

        // Add optional properties
        if (null !== $this->getDescription()) {
            $this->output .= 'DESCRIPTION:' . $this->getDescription() . self::DELIMITER;
        }
        if (null !== $this->getLocation()) {
            $this->output .= 'LOCATION:' . $this->getLocation() . self::DELIMITER;
        }
        if (null !== $this->getCategories()) {
            $this->output .= 'CATEGORIES:' . implode(', ', $this->getCategories()) . self::DELIMITER;
        }

        // End iCalendar event
        $this->output .= 'END:VEVENT' . self::DELIMITER;

        return $this->output;
    }

    /**
     * {@inheritdoc}
     */
    public function getDtEnd()
    {
        return $this->dtEnd;
    }

    /**
     * {@inheritdoc}
     */
    public function getDtStamp()
    {
        return $this->dtStamp;
    }

    /**
     * {@inheritdoc}
     */
    public function getDtStart()
    {
        return $this->dtStart;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Returns the event's description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the event's location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * {@inheritdoc}
     * @return string Event uid
     */
    public function getUID()
    {
        return $this->uid;
    }

    /**
     * {@inheritdoc}
     */
    public function setDtEnd(\DateTime $datetime)
    {
        $this->dtEnd = $this->formatDateTime($datetime);
    }

    /**
     * {@inheritdoc}
     */
    public function setDtStamp(\DateTime $datetime)
    {
        $datetime->setTimezone(new \DateTimeZone('UTC'));
        $dtStamp = $datetime->format('Ymd\THis\Z');

        $this->dtStamp = $dtStamp;
    }

    /**
     * {@inheritdoc}
     */
    public function setDtStart(\DateTime $datetime)
    {
        $this->dtStart = $this->formatDateTime($datetime);
    }

    /**
     * {@inheritdoc}
     */
    public function setSummary($text)
    {
        $this->summary = $text;
    }

    /**
     * Sets the event's description
     * @param string $text
     */
    public function setDescription($text)
    {
        $this->description = $this->folding($text);
    }

    /**
     * Sets the event's location
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * {@inheritdoc}
     */
    public function setUID($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Returns the event category
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Adds an event category
     *
     * @param string $category The event category
     */
    public function addCategory($category)
    {
        $this->categories[] = $category;
    }

    /**
     * Removes an event category
     *
     * @return boolean
     */
    public function removeCategory($event)
    {
        $key = array_search($event, $this->categories, true);

        if ($key !== false) {
            unset($this->categories[$key]);

            return true;
        }

        return false;
    }

    /**
     * Formats event datetime objects to a localized string
     *
     * @return string A localized datetime string
     * @see http://tools.ietf.org/html/rfc5545#section-3.3.5
     */
    protected function formatDateTime(\DateTime $datetime)
    {
        $dateFormatted = ':';

        // add time zone if present
        $timezone = $datetime->getTimezone();
        if (false !== $timezone) {
            $dateFormatted = ';TZID=' . $timezone->getName() . ':';
        }

        // Set date
        $dateFormatted .= $datetime->format('Ymd');

        // Add time if set
        $dateOnly = clone $datetime;
        $dateOnly->setTime(0, 0, 0);
        if ($datetime != $dateOnly) {
            $dateFormatted .= $datetime->format('\THis');
        }

        return $dateFormatted;
    }


    /**
     * Generates a unique identifier for iCalendar events
     *
     * @return string  A unique event identifier
     * @see http://tools.ietf.org/html/rfc5545#section-3.8.4.7
     */
    protected function generateUID()
    {
        $rand = substr(str_shuffle(md5(microtime())), 0, 10);
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR'];
        $uid = $rand . '@' . $host;

        return $uid;
    }

    /**
     * Folds content lines for event properties
     * Folding is automatically called by the setDesctiption method.
     * @see http://tools.ietf.org/html/rfc5545#section-3.1
     *
     * @param string $text A string representation
     * @return string A folded string
     */
    protected function folding($text)
    {
        return wordwrap($text, 75, self::DELIMITER . "\t");
    }

    /**
     * Unfolds content lines for event properties
     * Unfolding is NOT automatically applied to getter methods and must be
     * called explicitly.
     *
     * "\s" escape character is used as single linear white-space characters
     * are allowed.
     * @see http://tools.ietf.org/html/rfc5545#section-3.1
     *
     * @param string $text A string representation
     * @return string A folded string
     */
    protected function unfolding($text)
    {
        return preg_replace('/' . self::DELIMITER . '\s/', '', $text);
    }
}
