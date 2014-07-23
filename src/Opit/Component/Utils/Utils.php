<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Component\Utils;

/**
 * The Utils class is a helper class for all class in the project.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage Component
 */
class Utils
{

    /**
     * Get all values from specific key in a multidimensional array
     *
     * @param $key string
     * @param $arr array
     * @return array
     */
    public static function arrayValueRecursive($key, array $arr)
    {
        $val = array();
        array_walk_recursive($arr, function ($v, $k) use ($key, &$val) {
            if ($k == $key) {
                array_push($val, $v);
            }
        });

        return $val;
    }

    /**
     * Grouping an array collection by a counter
     *
     * @param  array   $collection
     * @param  integer $division
     * @return array   the grouped collection array
     */
    public static function groupingArrayByCounter($collection, $division)
    {
        $result = array();
        $counter = $i = 0;

        // Grouping collection by counter
        // The elements of collection will be ordered into subarrays by the division number.
        foreach ($collection as $data) {
            if ($counter % $division == 0) {
                $index = $i++;
            }
            $result[$index][] = $data;
            $counter++;
        }

        return $result;
    }

    /**
     * Extracts and returns a class basename
     *
     * @param  object $obj
     * @return string The class basename
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
     * Collects error messages from a symfony form object
     *
     * @param  \Symfony\Component\Form\Form $form
     * @return array                        An array containing form error messages
     */
    public static function getErrorMessages(\Symfony\Component\Form\Form $form, $i = null)
    {
        $errors = array();
        if (null === $i) {
            $i = 0;
        }
        foreach ($form->getErrors() as $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach ($parameters as $var => $value) {
                $template = str_replace($var, $value, $template);
            }

            $errors[$i] = $template;
            $i++;
        }
        if ($form->count()) {
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    $errors = array_merge($errors, self::getErrorMessages($child, $i));
                }
            }
        }

        return $errors;
    }

    /**
     *  Validate date
     *
     * @link http://hu1.php.net/checkdate#113205
     * @param  date    $date
     * @param  string  $format the format of the date
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
     * @param  string  $currencyString this contains the currencies.
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

    /**
     * Get all dates between two datetime.
     *
     * @param  \DateTime $sDate the start date
     * @param  \DateTime $eDate the end date
     * @return array     of datetimes
     */
    public static function diffDays($sDate, $eDate)
    {
        $startDate = clone $sDate;
        $endDate = clone $eDate;
        $days = array();

        // Collect the days between two datetime.
        while ($startDate->getTimestamp() <= $endDate->getTimestamp()) {
            $days[] = clone $startDate;
            $startDate->add(new \DateInterval("P1D"));
        }

        return $days;
    }

    /**
     * Builds/extends a doctrine query based on params
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param array $input The input query params
     * @param array $params The generated query params
     * @param array $andX An array of query expressions
     * @param string|null $alias The alias used for properties
     */
    public static function buildDoctrineQuery(\Doctrine\ORM\QueryBuilder $qb, array $input, array &$params, array &$andx, $alias = null)
    {
        if (null === $alias) {
            $alias = current($qb->getRootAliases());
        }

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $qb->leftJoin("{$alias}.{$key}", $key[0]);
                self::buildDoctrineQuery($qb, $value, $params, $andx, $key[0]);
            } else {
                // To workaround empty form posts for search criteria expecting no values, the "NULL" value can be used.
                // Posted NULL values will be excluded from search.
                if ($value != '' && $value != 'NULL') {
                    $params[$key] = '%'.$value.'%';
                    $andx[] = $qb->expr()->andX($qb->expr()->like("{$alias}.{$key}", ':'.$key));
                }
            }
        }
    }

    /**
     * Count weekend days in a date range
     *
     * @param TimeStamp $start
     * @param TimeStamp $end
     * @return integer
     */
    public static function countWeekendDays($start, $end)
    {
        $count = 0;

        for ($i = $start; $i <= $end; $i = $i + 86400) {
            if (Date('D', $i) == 'Sat' || Date('D', $i) == 'Sun') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Convert array values to upper case
     *
     * @param mixed $arr
     * @return array
     */
    public static function arrayValuesToUpper($arr)
    {
        // Convert value to array
        if (!is_array($arr)) {
            $arr = array($arr);
        }

        // Ensure capitalized role names
        array_walk($arr, function (&$value, $index) {
            $value = strtoupper($value);
        });

        return $arr;
    }

    /**
     * Sanitizes strings by removing special chars (e.g. file names)
     *
     * @param string $text
     * @return string sanitized file name
     */
    public static function sanitizeString($text)
    {
        // Replace spaces first
        $text = preg_replace('/\s+/', '', $text);
        // Remove any special characters
        $text = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $text);

        return strtolower($text);
    }

    /**
     * Get the higher level roles which extend the given role in the role hierarchy.
     *
     * @param mixin $hierarchy from the security.
     * @param string $role
     * @return array the higher roles.
     */
    public static function getHigherLevelRoles($hierarchy, $role = 'ROLE_USER')
    {
        $higherRoles = array(strtoupper($role));
        // Get those roles which contain the $role.
        foreach ($hierarchy as $key => $value) {
            // If the two array have common values.
            if (array_intersect($higherRoles, $value)) {
                $higherRoles[] = $key;
            }
        }

        return $higherRoles;
    }
}
