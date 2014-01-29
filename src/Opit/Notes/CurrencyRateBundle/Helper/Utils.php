<?php

namespace Opit\Notes\CurrencyRateBundle\Helper;

/**
 * The Utils class is a helper class for all class in the project.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class Utils
{
    /**
     * Extracts and returns a class basename
     *
     * @param object $obj
     * @return string  The class basename
     */
    public static function getClassBasename($obj)
    {
        $classname = get_class($obj);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }
    
    /**
     *  Validate date
     * 
     * @link http://hu1.php.net/checkdate#113205
     * @param date $date
     * @param string $format the format of the date
     * @return boolean true or false
     */
    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $default = \DateTime::createFromFormat($format, $date);
        return $default && $default->format($format) == $date;
    }
}
