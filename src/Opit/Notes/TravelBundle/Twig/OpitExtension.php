<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Twig;

/**
 * Twig OpitExtension class
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class OpitExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'formatAmount' => new \Twig_Function_Method($this, 'formatAmount')
        );
    }

    /**
     * 
     * @param integer $amount
     * @param integer $decimals
     * @param string $decPoint
     * @param string $thousandSep
     * @param string $currency
     * @return int
     */
    public function formatAmount($amount, $decimals, $decPoint, $thousandSep, $currency)
    {
        if (0 == $amount) {
            return 0;
        }
        if ('HUF' == $currency) {
            return number_format($amount, 0, $decPoint, $thousandSep);
        }
        
        return number_format($amount, $decimals, $decPoint, $thousandSep);
    }
   

    public function getName()
    {
        return 'opit_travel_bundle_extension';
    }
}

?>
