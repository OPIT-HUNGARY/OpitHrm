<?php

namespace Opit\Notes\TravelBundle\Helper;

/**
 * The Utils class is a helper class for all class in the project.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class Utils {

    /**
     * Get all values from specific key in a multidimensional array
     *
     * @param $key string
     * @param $arr array
     * @return array
     */
    public static function arrayValueRecursive($key, array $arr) {
        $val = array();
        array_walk_recursive($arr, function($v, $k) use($key, &$val) {
            if ($k == $key) {
                array_push($val, $v);
            }
        });
        
        return $val;
    }

}
