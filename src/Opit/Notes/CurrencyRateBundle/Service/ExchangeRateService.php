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

use Opit\Notes\CurrencyRateBundle\Entity\Currency;
use Opit\Notes\CurrencyRateBundle\Entity\Rate;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;

/**
 * This class is a service for the ChangeRateBundle to get the exchange rates.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage ChangeRateBundle
 */
class ExchangeRateService
{
    /**
     * em 
     * @var EntityManager 
     */
    protected $em;

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
     * @param string $currency type of Currency
     */
    public function __construct(EntityManager $entityManager, $hufRate, $mnbUrl)
    {
        $this->em = $entityManager;
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
     * Get the current exchange rates from MNB
     * @return array $currencyRates the current exchange rates
     */
    public function getCurrentExchangeRatesFromMnb()
    {
        //Strore the current exchante rates
        $currencyRates = array();

        //Soap client to download informations from MNB
        $client = new \SoapClient($this->mnbUrl);
        //Get the current exchange rates from the response
        $response = $client->__soapCall("GetCurrentExchangeRates", array());

        //DOM document
        $dom = new \DOMDocument();
        //Load the current exchange rates into a DOM
        $dom->loadXML($response->GetCurrentExchangeRatesResult);
        //DOMXpath to query the dom document
        $xpath = new \DOMXPath($dom);
        //Find the rates in the DOM with xpath query
        $query = "//MNBCurrentExchangeRates/Day/Rate";
        //Get the rates from the DOM
        $entries = $xpath->query($query);

        //Iterate the rates and save the currency with its actural rate
        foreach ($entries as $entry) {
            $currencyRates[$entry->getAttribute('curr')] = str_replace(',', '.', $entry->nodeValue);
        }
        return $currencyRates;
    }
    
    /**
     * Save the current exchange rates to database
     */
    public function saveCurrentExchangeRates()
    {
        $currencies = $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')->findAll();
        $currencyRates = $this->getCurrentExchangeRatesFromMnb();
        
        foreach ($currencies as $currency) {
            //currency's code
            $code = $currency->getCode();
            //if currency in currency rates array then create rate
            if (array_key_exists($code, $currencyRates)) {
                $rate = new Rate();
                //create rate for today
                if (!$this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->hasRate($code, new \DateTime('today'))) {
                    $rate->setCurrencyCode($currency);
                    $rate->setRate($currencyRates[$code]);
                    $this->em->persist($rate);
                } else {
                    $rate = $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')
                                       ->findRateByCodeAndDate($code, new \DateTime('today'));
                    $rate->setRate($currencyRates[$code]);
                    $this->em->persist($rate);
                }

                //create rate for tomorrow (next day)
                if (!$this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->hasRate($code, new \DateTime('tomorrow'))) {
                    $rateForTomorrow = clone $rate;
                    $rateForTomorrow->setCreated(new \DateTime('tomorrow'));
                    $rateForTomorrow->setUpdated(new \DateTime('tomorrow'));
                    $this->em->persist($rateForTomorrow);
                } else {
                    $rateForTomorrow = $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')
                                                  ->findRateByCodeAndDate($code, new \DateTime('tomorrow'));
                    $rateForTomorrow->setRate($currencyRates[$code]);
                    $rateForTomorrow->setCreated(new \DateTime('tomorrow'));
                    $rateForTomorrow->setUpdated(new \DateTime('tomorrow'));
                    $this->em->persist($rateForTomorrow);
                }
            }
        }
        $this->em->flush();
    }
}
