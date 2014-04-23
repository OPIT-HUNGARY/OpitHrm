<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Model;

/**
 * Exchange rate interface.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage CurrencyRateBundle
 */
interface ExchangeRateInterface
{
    /**
     * Converts the given value into the requested currency by a given date
     * 
     * @param string $originCode The value's origin currency code
     * @param string $destinationCode The currency code to be converted into
     * @param float $value The amount
     * @param \DateTime $datetime The rate's datetime object
     * @return float
     */
    public function convertCurrency($originCode, $destinationCode, $value, \DateTime $datetime = null);

    /**
     * Returns rates for a given date
     * 
     * @param \DateTime $datetime The datetime object for reqested rates
     * @return array
     */
    public function getRatesByDate(\DateTime $date = null);

    
    /**
     * Returns the rate for the requested currency by a given date
     * 
     * @param string $code the currency code
     * @param \DateTime $datetime the searched datetime
     * @return float
     */
    public function getRateOfCurrency($code, \DateTime $datetime = null);

    /**
     * Returns all rates (filtered by options)
     * 
     * @param array $options Options used for rate filtering
     */
    public function getExchangeRates($options);
}
