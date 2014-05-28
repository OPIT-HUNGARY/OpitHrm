<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Twig CoreExtension class
 *
 * @author OPIT Consulting Kft. - NOTES/TAO Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class CoreExtension extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('humanize', array($this, 'humanizeFilter')),
            new \Twig_SimpleFilter('underscore', array($this, 'underscoreFilter')),
            new \Twig_SimpleFilter('camelize', array($this, 'camelizeFilter')),
            new \Twig_SimpleFilter('classname', array($this, 'classnameFilter'))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'strpos' => new \Twig_Function_Method($this, 'strpos'),
            'splitText' => new \Twig_Function_Method($this, 'splitText'),
            'bundleExist' => new \Twig_Function_Method($this, 'bundleExist'),
            'base64Encode' => new \Twig_Function_Method($this, 'base64Encode')
        );
    }

    /**
     * Returns the given underscored_word_group as a Human Readable Word Group.
     * (Underscores are replaced by spaces and capitalized following words.)
     *
     * @param  string $lowerCaseAndUnderscoredWord
     * @return string Human readable string
     */
    public function humanizeFilter($lowerCaseAndUnderscoredWord)
    {
        $result = ucwords(str_replace('_', ' ', $lowerCaseAndUnderscoredWord));

        return $result;
    }

    /**
     * Returns the given lower_case_and_underscored_word as a CamelCased word.
     *
     * @param  string $lowerCaseAndUnderscoredWord
     * @return string Camelized word
     */
    public function camelizeFilter($lowerCaseAndUnderscoredWord)
    {
        $result = str_replace(' ', '', $this->humanizeFilter($lowerCaseAndUnderscoredWord));

        return $result;
    }

    /**
     * Returns the given camelCasedWord as an underscored_word.
     *
     * @param  string $camelCasedWord
     * @return string Underscore syntaxed string
     */
    public function underscoreFilter($camelCasedWord)
    {
        $result = strtolower(preg_replace('/([A-Z]?)([A-Z][a-z])/', '$1_$2', $camelCasedWord));

        return $result;
    }

    /**
     * Returns the position of where the needle exists relative to the beginning of the haystack string
     *
     * @param  type $haystack
     * @param  type $needle
     * @return type
     */
    public function strpos($haystack, $needle)
    {
       $result =  strpos($haystack, (string) $needle);

       return $result;
    }

    /**
     *
     * @param  string  $text      the string you want to split
     * @param  sting   $delimiter the pattern at where you want to split the text
     * @param  integer $index     which index of the splitted text to return
     * @return type
     */
    public function splitText($text, $delimiter, $index)
    {
        $result = explode($delimiter, $text);

        return $result[$index];
    }

    /**
     * To check if bundle exist
     *
     * @param  type $bundle
     * @return boolean
     */
    public function bundleExist($bundle)
    {
        if (array_key_exists($bundle, $this->container->getParameter('kernel.bundles'))) {
            return true;
        }
        return false;
    }

    /**
     * Encoding to base 64
     *
     * @param  string $data
     * @return boolean
     */
    public function base64Encode($data)
    {
        return base64_encode($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'core_extension';
    }

}
