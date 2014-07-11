<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Bridge\Monolog\Logger;
use Opit\Component\Utils\Utils;
use Opit\Notes\CurrencyRateBundle\Service\AbstractExchangeRateService;

/**
 * This class is a service for the CurrencyRateBundle to get the exchange rates
 * from the Hungarian National Bank (MNB).
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 * @see AbstractExchangeRateService
 */
class MNBExchangeRateService extends AbstractExchangeRateService
{
    /**
     * Default rate of the HUF
     * @var float
     */
    private $hufRate;

    /**
     * Url of the MNB webservice
     * @var string
     */
    private $mnbUrl;

    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param float $hufRate Hungarian currency rate.
     * @param string $mnbUrl MNB's url
     */
    public function __construct(EntityManager $entityManager, Logger $logger, $hufRate, $mnbUrl)
    {
        parent::__construct($entityManager, $logger);
        $this->hufRate = (float) $hufRate;
        $this->mnbUrl = $mnbUrl;
    }

    /**
     * Getter method for hufRate field
     *
     * @return float
     */
    public function getHufRate()
    {
        return $this->hufRate;
    }

    /**
     * Convert Currencies.
     *
     * The HUF currency rate does not exist in the MNB response.
     * {@internal}
     * @see AbstractExchangeRateService::getRateOfCurrency()
     */
    public function convertCurrency($originCode, $destinationCode, $value, \DateTime $datetime = null)
    {
        // If originCode is equal to be destinationCode then return with the value
        if ($originCode === $destinationCode) {
            return $value;
        }

        // If destinationCode is HUF then the rate will be 1
        if ('HUF' === strtoupper($destinationCode)) {
            $destinationRate = $this->getHufRate();
        } else {
            $destinationRate = $this->getRateOfCurrency($destinationCode, $datetime);
        }
        $result = (float) $value / $destinationRate;

        // If originCode is not HUF then convert to HUF currency
        if ('HUF' !== strtoupper($originCode)) {
            $originRate = $this->getRateOfCurrency($originCode, $datetime);
            $result = (float) $result * $originRate;
        }

        return $result;
    }

    /**
     * fetch exchange rates from MNB By start date, end date, and currency codes.
     *
     * <code>
     * $options = array(
     *   'startDate' => '2014-01-20',
     *   'endDate' => '2014-01-27',
     *   'currencyNames' => 'EUR,GBP,USD'
     * );
     * </code>
     * @see validateOptions()
     * @see AbstractExchangeRateService::setExchangeRates()
     * @param array[string]string $options the parameters
     * @return array[string][string]float|bool the result array of the currencies with dates and rates,
     *         or false if the response was empty.
     */
    public function fetchExchangeRates($options)
    {
        $this->logger->info(
            sprintf('[|%s] Starting the fetch exchange rates from MNB.', Utils::getClassBasename($this))
        );
        // Validate the options data
        $options = $this->validateOptions($options);

        // If $options is false return.
        if (false === $options) {
            return false;
        }
        // Strore the current exchante rates
        $currencyRates = array();
        // Soap client to download informations from MNB
        $client = new \SoapClient($this->mnbUrl);
        // Get the current exchange rates from the response
        $response = $client->__soapCall("GetExchangeRates", array('parameters' => $options));

        if (empty($response)) {
            $this->logger->error(
                sprintf(
                    '[|%s] Soap client could not fetch rates from MNB.',
                    Utils::getClassBasename($this)
                )
            );
            return false;
        } elseif ('<MNBExchangeRates />' === $response) {
            $this->logger->alert(
                sprintf(
                    '[|%s] Empty response has been received.',
                    Utils::getClassBasename($this)
                )
            );
            return false;
        }

        // DOM document
        $dom = new \DOMDocument();
        // Load the current exchange rates into a DOM
        $dom->loadXML($response->GetExchangeRatesResult);
        // DOMXpath to query the dom document
        $xpath = new \DOMXPath($dom);
        // Find the rates in the DOM with xpath query
        $query = "//MNBExchangeRates/Day";
        // Get the rates from the DOM
        $entries = $xpath->query($query);
        // Iterate the days
        foreach ($entries as $entry) {

            // Iterate the rates
            foreach ($entry->childNodes as $child) {
                $currencyRates[$child->getAttribute('curr')][$entry->getAttribute('date')] =
                    (float) \str_replace(',', '.', $child->nodeValue);
            }
        }

        $this->logger->info(
            sprintf('[|%s] Exchange rates retrieved from MNB.', Utils::getClassBasename($this))
        );
        $this->setExchangeRates($currencyRates);

        return $this->currencyRates;
    }

    /**
     * @internal
     */
    public function fetchCurrentExchangeRates()
    {
        $this->logger->info(
            sprintf('[|%s] Starting to fetch exchange rates from MNB.', Utils::getClassBasename($this))
        );

        // Set the dates to today
        $options = array(
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d')
        );

        return $this->fetchExchangeRates($options);
    }

    /**
     * Validate the option fields.
     *
     * @param mixed $options the option fields which will be validated.
     * @throws MissingMandatoryParametersException
     *
     * @return mixed|boolean with the $options array or false if there was invalid arguments.
     */
    private function validateOptions($options)
    {
        // If the startDate is not setted or it is empty then throw exception
        if (!isset($options['startDate']) || empty($options['startDate'])) {
            $this->logger->error(
                sprintf('[|%s] Start date is missing in the argument list.', Utils::getClassBasename($this))
            );
            throw new MissingMandatoryParametersException(
                'The "startDate" parameter is missing in the argument list!'
            );
        } elseif ($options['startDate'] > date('Y-m-d')) {
            // If the start date is future then return with empty array
            $this->logger->alert(
                sprintf('[|%s] Start date is a future date in the argument list.', Utils::getClassBasename($this))
            );
            return false;

        } elseif (!Utils::validateDate($options['startDate'], 'Y-m-d')) {
            $this->logger->alert(
                sprintf('[|%s] The start option is in invalid date format.', Utils::getClassBasename($this))
            );
            return false;
        }

        // If the endDate is not setted or it is empty then set today
        if (!isset($options['endDate']) || empty($options['endDate'])) {
             $options['endDate'] = date('Y-m-d');

        } elseif (!Utils::validateDate($options['endDate'], 'Y-m-d')) {
            $this->logger->alert(
                sprintf('[|%s] The end option is in invalid date format.', Utils::getClassBasename($this))
            );
            return false ;
        }

        // If the currencyNames is not set in the options array or it is empty, it is gotten the currencies from the DB
        if (!isset($options['currencyNames']) || empty($options['currencyNames'])) {
            $options['currencyNames'] = implode(
                ',',
                $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')->getAllCurrencyCodes()
            );
        } elseif (!Utils::validateCurrencyCodesString($options['currencyNames'])) {
            $this->logger->alert(
                sprintf('[|%s] The currency option is in invalid format.', Utils::getClassBasename($this))
            );
            return false;
        } else {
            $options['currencyNames'] = strtoupper($options['currencyNames']);
        }

        return $options;
    }
}
