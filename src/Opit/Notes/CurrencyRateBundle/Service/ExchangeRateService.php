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

use Opit\Notes\CurrencyRateBundle\Entity\Rate;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\CommonException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Bridge\Monolog\Logger;
use Opit\Notes\CurrencyRateBundle\Helper\Utils;
use Opit\Notes\CurrencyRateBundle\Model\ExchangeRateInterface;

/**
 * This class is a service for the ChangeRateBundle to get the exchange rates.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage CurrencyRateBundle
 */
class ExchangeRateService implements ExchangeRateInterface
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

    /**
     * Currency rates
     * @var mixin 
     */
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
    
    public function getRatesByDate(\DateTime $date = null)
    {
        // If datetime is null set to today
        if (null === $date) {
            $date = new \DateTime('today');
        }
        
        return $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->getRatesArray($date);
    }
    
   /**
    * Get rate of a currency by currency code
    * The time can be setted, if it null the method will search on the rate of today.
    * 
    * @param string $code the currency code
    * @param \DateTime $datetime the searched datetime
    * @return float rate
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
            throw new CommonException(sprintf('Rate entity not found for "%s, %s"', $code, $datetime->format('Y-m-d')));
        }
        
        return $rate->getRate();
    }
    
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
    public function setExchangeRates(array $exchangeRates)
    {
        $this->logger->info(sprintf('[|%s] Starting set exchange rates by manually.', Utils::getClassBasename($this)));
        $currencyRates = array();
        // get the exist currency codes.
        $currencyCodes = $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')->getAllCurrencyCodes();
        foreach ($exchangeRates as $currencyCode => $dates) {
            
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
        
        // validate the options data
        $options = $this->validateOptions($options);
        
        // if $options is false return.
        if (false === $options) {
            return false;
        }
        //Strore the current exchante rates
        $currencyRates = array();
        //Soap client to download informations from MNB
        $client = new \SoapClient($this->mnbUrl);
        //Get the current exchange rates from the response
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
        
        $this->setExchangeRates($currencyRates);

        return $this->currencyRates;
    }
    
    /**
     * Get the last local rate's date
     * 
     * @throws \Doctrine\Common\CommonException for not found the last rate.
     * @return \DateTime the last local rate's date
     */
    public function getLastLocalRateDate()
    {
        $rate =  $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->findLastRate();
       
        //if rate is null throw an exception
        if (null === $rate) {
            $this->logger->error(
                sprintf('[|%s] Rate entity not found. (Empty database)', Utils::getClassBasename($this))
            );
            throw new CommonException('Rate entity not found (empty database).');
        }
        
        return $rate->getCreated()->setTime(0, 0, 0);
    }
    
    /**
     * Get the first local rate's date
     * 
     * @throws \Doctrine\Common\CommonException for not found the last rate.
     * @return \DateTime the last local rate's date
     */
    public function getFirstLocalRateDate()
    {
        $rate =  $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->findFirstRate();
       
        //if rate is null throw an exception
        if (null === $rate) {
            $this->logger->error(
                sprintf('[|%s] Rate entity not found. (Empty database)', Utils::getClassBasename($this))
            );
            throw new CommonException('Rate entity not found (empty database).');
        }
        
        return $rate->getCreated()->setTime(0, 0, 0);
    }
    
    /**
     * Get the Missing rates
     * From the last rate date of localdatabase + 1 Till Today - 1
     * 
     * @return array|bool the currency rates arrary, or false if the respons was empty from the remote.
     */
    public function getMissingExchangeRates($options)
    {
        // Initialize the start and end datetimes
        $startDate = $this->getLastLocalRateDate();
        $todayDate = date('Y-m-d', strtotime('today'));
        $tomorrowDate = date('Y-m-d', strtotime('tomorrow'));
        
        // If the last local rate's date is today or tomorrow then aborting.
        if ($todayDate === $startDate->format('Y-m-d') || $tomorrowDate === $startDate->format('Y-m-d')) {
            return false;
        }
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
     * Get the diffed rates
     * From the last rate date of localdatabase + 1 Till Today - 1
     * 
     * @return array|bool the currency rates arrary, or false if the respons was empty from the remote.
     */
    public function getDiffExchangeRates($options)
    {
        if (!isset($options['startDate']) || empty($options['startDate'])) {
            $startDate = $this->getFirstLocalRateDate();
            $options['startDate'] = $startDate->format('Y-m-d');
        }
        return $this->getExchangeRates($options);
    }
    
    /**
     * Save exchange rates.
     * 
     * @return false if saving didn't happen because of the rates are empty
     */
    public function saveExchangeRates($force = false, array $currencyRates = array())
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
                        
                        // Persist rates for date differences between current and last date (weekend)
                        $interval = date_diff($lastDateObj, $dateObj);
                        $this->setMissingRatesAndPersist(
                            $lastDateObj,
                            $interval->format('%d')-1,
                            $currencyCode,
                            $value,
                            $force
                        );
                       
                        // Persist the current rate
                        $rate = $this->createOrUpdateRate($currencyCode, $value, $dateObj, $force);
                        if (null !== $rate) {
                            $this->em->persist($rate);
                        }
                        $lastDateObj = clone $dateObj;
                    }
                    
                    // Insert difference of last MNB date and tomorrow using last MNB rate
                    $tomorrow = new \DateTime('tomorrow');
                    $difference = $tomorrow->diff($lastDateObj);
                    $this->setMissingRatesAndPersist(
                        $lastDateObj,
                        $difference->format('%d'),
                        $currencyCode,
                        $value,
                        $force
                    );
                }
                $this->em->flush();

                $this->logger->info(
                    sprintf('[|%s] Rates synced successfully.', Utils::getClassBasename($this))
                );

            } catch (Exception $exc) {

                $this->logger->error(
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
     * Create rates for MNB date differences.
     * 
     * @param \DateTime $date
     * @param integer $days
     * @param string $currencyCode
     * @param float $value
     * @param boolean $force
     */
    private function setMissingRatesAndPersist(\DateTime $date, $days, $currencyCode, $value, $force)
    {
        $rateDate = clone $date;
        
        // Insert difference of last MNB date and tomorrow using last MNB rate
        for ($i = 0; $i < $days; $i++) {
            $rateDate->add(new \DateInterval('P1D'));
            $rate = $this->createOrUpdateRate($currencyCode, $value, clone $rateDate, $force);
            if (null !== $rate) {
                $this->em->persist($rate);
            }
        }
    }
    
    /**
     * Create or Update a rate object.
     * 
     * @param string $currencyCode currency code.
     * @param float $value the value of the rate.
     * @param \DateTime $dateObj date of the rates.
     * @param boolean force for a force save to the database.
     * 
     * @return \Opit\Notes\CurrencyRateBundle\Entity\Rate|null $rate object or null.
     */
    private function createOrUpdateRate($currencyCode, $value, \DateTime $dateObj, $force)
    {
        if (!$this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->hasRate($currencyCode, $dateObj)) {
            $rate = new Rate();
            $currency = $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')
                                ->findOneByCode($currencyCode);
            $rate->setCurrencyCode($currency);
        } else {
            $rate = $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')
                               ->findRateByCodeAndDate($currencyCode, $dateObj);
            
            //If this is not a force update or the rate's value wasn't changed skipt the update.
            if (!$force && $rate->getRate() === $value) {
                return null;
            }
        }
        $rate->setRate($value);
        $rate->setCreated($dateObj);
        $rate->setUpdated(new \DateTime('now'));
        
        return $rate;
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
        
        // if the endDate is not setted or it is empty then set today
        if (!isset($options['endDate']) || empty($options['endDate'])) {
             $options['endDate'] = date('Y-m-d');
             
        } elseif (!Utils::validateDate($options['endDate'], 'Y-m-d')) {
            $this->logger->alert(
                sprintf('[|%s] The end option is in invalid date format.', Utils::getClassBasename($this))
            );
            return false ;
        }
        // if the currencyNames is not set in the options array or it is empty, it is gotten the currencies from the DB
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
