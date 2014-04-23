<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Helper;

/**
 * The Utils class is a helper class for all class in the project.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage CurrencyRateBundle
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
    
    /**
     * Validate the currency string.
     * Check the currencies' lenght, only alphabetic or not, and separated by comma or not.
     * 
     * @param string $currencyString this contains the currencies.
     * @return boolean return true if the passed currency string is valid
     */
    public static function validateCurrencyCodesString($currencyString)
    {
        $currencies = explode(',', $currencyString);
        
        foreach ($currencies as $currency) {
            // If currency's length is not equel to 3 or it is not alphebitc return with false.
            if (3 != strlen($currency) || !ctype_alpha($currency)) {
                return false;
            }
        }
        return true;
    }
}
