<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Model;

/**
 * Exchange rate interface.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
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
     * Returns rates from the remote source (filtered by options)
     *
     * @param array $options Options used for rate filtering
     */
    public function fetchExchangeRates($options);

    /**
     * Fetch the current exchange rates from the remote source.
     *
     * @return array $currencyRates the current exchange rates
     */
    public function fetchCurrentExchangeRates();

    /**
     * Returns all rates from local database (filtered by options)
     *
     * @return array of \Opit\OpitHrm\CurrencyRateBundle\Entity\Rate objects
     */
    public function getExchangeRates($options);

    /**
     * Get the current exchange rates from local database.
     *
     * @return array of \Opit\OpitHrm\CurrencyRateBundle\Entity\Rate objects
     */
    public function getCurrentExchangeRates();

    /**
     * Get the rates by datetime
     *
     * @param \DateTime $date
     * @return array of \Opit\OpitHrm\CurrencyRateBundle\Entity\Rate
     */
    public function getRatesByDate(\DateTime $date = null);

    /**
     * Get rate of a currency by currency code
     * The time can be setted, if it null the method will search on the rate of today.
     *
     * @param string $code the currency code
     * @param \DateTime $datetime the searched datetime
     * @return float rate
     */
    public function getRateOfCurrency($code, \DateTime $datetime = null);

    /**
     * Get the last local rate's date
     *
     * @throws \Doctrine\Common\CommonException for not found the last rate.
     * @return \DateTime the last local rate's date
     */
    public function getLastLocalRateDate();

    /**
     * Get the first local rate's date
     *
     * @throws \Doctrine\Common\CommonException for not found the last rate.
     * @return \DateTime the last local rate's date
     */
    public function getFirstLocalRateDate();

    /**
     * Get the diffed rates
     * From the last rate date of localdatabase + 1 Till Today - 1
     *
     * @return array|bool the currency rates arrary, or false if the respons was empty from the remote.
     */
    public function getDiffExchangeRates($options);

    /**
     * Get the Missing rates
     * From the last rate date of localdatabase + 1 Till Today - 1
     *
     * @return array|bool the currency rates arrary, or false if the respons was empty from the remote.
     */
    public function getMissingExchangeRates($options);

    /**
     * Set the currency rates
     *
     * <code>
     * $exchangeRates = array(
     *     'CHF' => array(
     *         '2014-01-20' => 244.46,
     *         '2014-01-21' => 245.14,
     *     ),
     *     'EUR' => array(
     *         '2014-01-20' => 300.55,
     *         '2014-01-21' => 302.97,
     *     ),
     *   );
     * </code>
     * @param array $exchangeRates
     */
    public function setExchangeRates(array $exchangeRates);

    /**
     * Save exchange rates.
     *
     * <code>
     * $currencyRates = array(
     *     'CHF' => array(
     *         '2014-01-20' => 244.46,
     *         '2014-01-21' => 245.14,
     *     ),
     *     'EUR' => array(
     *         '2014-01-20' => 300.55,
     *         '2014-01-21' => 302.97,
     *     ),
     *   );
     * </code>
     * @param boolean $force force save to the database.
     * @param array $currencyRates currency rates.
     * @return false if saving didn't happen because of the rates are empty
     */
    public function saveExchangeRates($force = false, array $currencyRates = array());
}
