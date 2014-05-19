<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\StatusBundle\Helper;

/**
 * The Utils class is a helper class for all class in the project.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
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
        array_walk_recursive ($arr, function ($v, $k) use($key, &$val){
            if ($k == $key) {
                array_push($val, $v);
            }
        });
        
        return $val;
    }
    
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
     * Collects error messages from a symfony form object
     * 
     * @param \Symfony\Component\Form\Form $form
     * @return array An array containing form error messages
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
}
