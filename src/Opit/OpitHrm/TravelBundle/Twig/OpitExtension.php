<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Twig;

use Opit\Component\Utils\Utils;

/**
 * Twig OpitExtension class
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
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
     * Format the amount
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
        return Utils::getformattedAmount($amount, $decimals, $decPoint, $thousandSep, $currency);
    }
   

    public function getName()
    {
        return 'opit_travel_bundle_extension';
    }
}

?>
