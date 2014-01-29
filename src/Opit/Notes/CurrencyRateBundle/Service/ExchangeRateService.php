<?php

/*
 * This file is part of the ChangeRate Bundle
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Opit\Notes\CurrencyRateBundle\Service;

use Opit\Notes\CurrencyRateBundle\Entity\Rate;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Bridge\Monolog\Logger;
use Opit\Notes\CurrencyRateBundle\Helper\Utils;

/**
 * This class is a service for the ChangeRateBundle to get the exchange rates.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage CurrencyRateBundle
 */
class ExchangeRateService
{
    /**
     * em 
     * @var EntityManager 
     */
    protected $em;

    /**
     *
     * @var Logger 
     */
    protected $logger;

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

    private $currencyRates;
    
    /**
     * Constructor
     * @param string $currency type of Currency
     */
    public function __construct(EntityManager $entityManager, Logger $logger, $hufRate, $mnbUrl)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
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
     * Convert the value from the origin currency code to the a destination currency code
     * 
     * @param string $originCode the origin currency code
     * @param string $destinationCode the destination currency code
     * @param float $value the converting value
     * @param \DateTime $datetime the date of the rate
     * @return float the converted value.
     */
    public function convertCurrency($originCode, $destinationCode, $value, \DateTime $datetime = null)
    {
        // If originCode is equal to be destinationCode then return with the value
        if ($originCode === $destinationCode) {
            return $value;
        }
        
        // If destinationCode is HUF then the rate will be 1
        if ('HUF' === strtoupper($destinationCode)) {
            $destinationRate = $this->hufRate;
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
    * Get rate of a currency by currency code
    * The time can be setted, if it null the method will search on the rate of today.
    * 
    * @param string $code the currency code
    * @param \DateTime $datetime the searched datetime
    * @return integer rate
    */
    public function getRateOfCurrency($code, \DateTime $datetime = null)
    {
        // If datetime is null set to today
        if (null === $datetime) {
             $datetime = new \DateTime('today');
        }
        $rate = $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')
                                       ->findRateByCodeAndDate(strtoupper($code), $datetime);
        
        //if rate is null throw an exception
        if (null === $rate) {
            throw new EntityNotFoundException(sprintf('Rate entity not found for "%s, %s"', $code, $datetime));
        }
        
        return $rate->getRate();
    }
    
    /**
     * Set the currency rates
     * 
     * <code>
     * $exhcangeRates = array(
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
     * @param array $exhcangeRates
     */
    public function setExchangeRates(array $exhcangeRates)
    {
        $this->logger->info(sprintf('[|%s] Starting set exchange rates by manually.', Utils::getClassBasename($this)));
        $currencyRates = array();
        // get the exist currency codes.
        $currencyCodes = $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')->getAllCurrencyCodes();
        foreach ($exhcangeRates as $currencyCode => $dates) {
            
            // if the currency code exist then go forward else skip out it.
            if (in_array($currencyCode, $currencyCodes)) {
                // iterate the dates
                foreach ($dates as $date => $value) {
                    // if date is valid then save it with the value else skip it.
                    if (Utils::validateDate($date, 'Y-m-d')) {
                        $currencyRates[strtoupper($currencyCode)][$date] = (float) \str_replace(',', '.', $value);
                    } else {
                        $this->logger->alert(sprintf('This %s is not a valid date format.', $date));
                    }
                }
            } else {
                $this->logger->alert(sprintf('This %s currency code does not exist.', $currencyCode));
            }
        }

        $this->currencyRates = $currencyRates;
    }
    
    /**
     * Get the current exchange rates from MNB
     * @return array $currencyRates the current exchange rates
     */
    public function getCurrentExchangeRates()
    {
        $this->logger->info(
            sprintf('[|%s] Starting to fetch exchange rates from MNB.', Utils::getClassBasename($this))
        );
        
        // Set the dates to today
        $options = array(
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d')
        );
        return $this->getExchangeRates($options);
    }

   /**
    * Get exchange rates from MNB By start date, end date, and currency codes.
    * 
    * <code>
    * $options = array(
    *   'startDate' => '2014-01-20',
    *   'endDate' => '2014-01-27',
    *   'currencyNames' => 'EUR,GBP,USD'
    * );
    * </code>
    * 
    * @param array[string]string $options the parameters
    * @return array[string][string]float|bool the result array of the currencies with dates and rates,
    *         or false if the response was empty.
    */
    public function getExchangeRates($options)
    {
        $this->logger->info(
            sprintf('[|%s] Starting the fetch exchange rates from MNB.', Utils::getClassBasename($this))
        );
        
        // if the paramter is missing in the argument list.
        if (!isset($options)) {
            $this->logger->error(
                sprintf('[|%s] Parameter is missing in the argument list.', Utils::getClassBasename($this))
            );
            throw new MissingMandatoryParametersException(
                'The parameter is missing in the argument list!'
            );
        }
        // if the startDate is not setted or it is empty then throw exception
        if (!isset($options['startDate']) || empty($options['startDate'])) {
            $this->logger->error(
                sprintf('[|%s] Start date is missing in the argument list.', Utils::getClassBasename($this))
            );
            throw new MissingMandatoryParametersException(
                'The "startDate" parameter is missing in the argument list!'
            );
        } elseif ($options['startDate'] > date('Y-m-d')) {
            // If the start date is future then return with empty array
            $this->logger->error(
                sprintf('[|%s] Start date is a future date in the argument list.', Utils::getClassBasename($this))
            );
            return array();
        }
        // if the endDate is not setted or it is empty then set today
        if (!isset($options['endDate']) || empty($options['endDate'])) {
             $options['endDate'] = date('Y-m-d');
        }
        // if the currencyNames is not set in the options array or it is empty, it is gotten the currencies from the DB
        if (!isset($options['currencyNames']) || empty($options['currencyNames'])) {
            $options['currencyNames'] = implode(
                ',',
                $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')->getAllCurrencyCodes()
            );
        }
        
        //Strore the current exchante rates
        $currencyRates = array();
        //Soap client to download informations from MNB
        $client = new \SoapClient($this->mnbUrl);
        //Get the current exchange rates from the response
        $response = $client->__soapCall("GetExchangeRates", array('parameters' => $options));

        if (empty($response) || '<MNBExchangeRates />' === $response) {
            $this->logger->error(
                sprintf(
                    '[|%s] Soap client could not fetch rates from MNB (empty response).',
                    Utils::getClassBasename($this)
                )
            );
            return false;
        }
        
        //DOM document
        $dom = new \DOMDocument();
        //Load the current exchange rates into a DOM
        $dom->loadXML($response->GetExchangeRatesResult);
        //DOMXpath to query the dom document
        $xpath = new \DOMXPath($dom);
        //Find the rates in the DOM with xpath query
        $query = "//MNBExchangeRates/Day";
        //Get the rates from the DOM
        $entries = $xpath->query($query);
        
        //Iterate the days
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
        
        $this->currencyRates = $currencyRates;
        
        return $this->currencyRates;
    }
    
    /**
     * Get the last local rate's date
     * 
     * @throws \Doctrine\ORM\EntityNotFoundException for not found the last rate.
     * @return \DateTime the last local rate's date
     */
    public function getLastLocalRateDate()
    {
        $rate =  $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->findLastRate();
       
        //if rate is null throw an exception
        if (null === $rate) {
            throw new EntityNotFoundException('Rate entity not found (empty database).');
            $this->logger->error(
                sprintf('[|%s] Rate entity not found. (Empty database)', Utils::getClassBasename($this))
            );
        }
        
        return $rate->getCreated()->setTime(0, 0, 0);
    }
    
    /**
     * Get the Missing rates
     * From the last rate date of localdatabase + 1 Till Today - 1
     * 
     * @return array|bool the currency rates arrary, or false if the respons was empty from the remote.
     */
    public function getMissingExchangeRates()
    {
        // Initialize the start and end datetimes
        $startDate = $this->getLastLocalRateDate();
        $endDate = new \DateTime('yesterday');
        
        // Set up the options parameter array
        $options = array(
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currencyCodes' => implode(
                ',',
                $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')->getAllCurrencyCodes()
            )
        );
        
        return $this->getExchangeRates($options);
    }
    
    /**
     *  Save exchange rates.
     * 
     * @return false if saving didn't happen because of the rates are empty
     */
    public function saveExchangeRates(array $currencyRates = array())
    {
        $this->logger->info(sprintf('[|%s] Rates sync is started.', Utils::getClassBasename($this)));
        
        if (!empty($currencyRates)) {
            $this->currencyRates = $currencyRates;
        }

        if (!empty($this->currencyRates)) {
            
            try {
                // Iterate the currencies
                foreach ($this->currencyRates as $currencyCode => $dates) {

                    $lastDateObj = new \DateTime(key($dates));
                    // Iterate the date and rate
                    foreach ($dates as $date => $value) {
                        $dateObj = new \DateTime($date);

                        // Persist rates for date differences between current and last date
                        $interval = date_diff($lastDateObj, $dateObj);
                        $days = $interval->format('%d');

                        if ($days > 1) {
                            for ($i=1; $i < $days; $i++) {
                                $lastDateObj->add(new \DateInterval('P1D'))->setTime(0, 0, 0);
                                $rate = $this->createOrUpdateRate($currencyCode, $value, clone $lastDateObj);
                                $this->em->persist($rate);
                            }
                        }

                        // Persist the current rate
                        $rate = $this->createOrUpdateRate($currencyCode, $value, $dateObj);
                        $this->em->persist($rate);

                        // If the date is today then save rates for tomorrow.
                        if (date('Y-m-d') === $date) {
                            $rateForTomorrow = $this->saveExchangeRatesForTomorrow($rate, $currencyCode, $value);
                            $this->em->persist($rateForTomorrow);
                        }

                        $lastDateObj = clone $dateObj;
                    }
                }
                $this->em->flush();

                $this->logger->info(
                    sprintf('[|%s] Rates synced successfully.', Utils::getClassBasename($this))
                );

            } catch (Exception $exc) {

                $this->logger->alert(
                    sprintf(
                        '[|%s] Rates synced FAILED! Error message: '. $exc->getTraceAsString(),
                        Utils::getClassBasename($this)
                    )
                );
            }
            
            return true;
        } else {
            $this->logger->alert(
                sprintf('[|%s] The currency rates array is empty.', Utils::getClassBasename($this))
            );
            
            return false;
        }
        
        $this->logger->info(sprintf('[|%s] Rates sync is ended.', Utils::getClassBasename($this)));
    }
    
    /**
     * Create or Update a rate object.
     * 
     * @param string $currencyCode currency code
     * @param float $value the value of the rate.
     * @param \DateTime $dateObj date of the rates
     * 
     * @return \Opit\Notes\CurrencyRateBundle\Entity\Rate $rate object.
     */
    private function createOrUpdateRate($currencyCode, $value, \DateTime $dateObj)
    {
        if (!$this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->hasRate($currencyCode, $dateObj)) {
            $rate = new Rate();
            $currency = $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')
                                ->findOneByCode($currencyCode);
            $rate->setCurrencyCode($currency);
        } else {
            $rate = $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')
                               ->findRateByCodeAndDate($currencyCode, $dateObj);
        }
        $rate->setRate($value);
        $rate->setCreated($dateObj);
        $rate->setUpdated($dateObj);
        
        return $rate;
    }
    
    /**
     * Save rates for tomorrow.
     * 
     * @param \Opit\Notes\CurrencyRateBundle\Entity\Rate $rate object.
     * @param string $code the currency's code
     * @param float $value the rate's value
     * 
     * @return \Opit\Notes\CurrencyRateBundle\Entity\Rate $rate object for tomorrow
     */
    private function saveExchangeRatesForTomorrow(Rate $rate, $code, $value)
    {
        //create rate for tomorrow (next day)
        if (!$this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->hasRate($code, new \DateTime('tomorrow'))) {
            $rateForTomorrow = clone $rate;

        } else {
            $rateForTomorrow = $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')
                                          ->findRateByCodeAndDate($code, new \DateTime('tomorrow'));
            $rateForTomorrow->setRate($value);
        }
        $rateForTomorrow->setCreated(new \DateTime('tomorrow'));
        $rateForTomorrow->setUpdated(new \DateTime('tomorrow'));
        
        return $rateForTomorrow;
    }
}
