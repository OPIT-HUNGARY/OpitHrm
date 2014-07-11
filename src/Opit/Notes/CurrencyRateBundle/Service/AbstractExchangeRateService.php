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
use Symfony\Bridge\Monolog\Logger;
use Opit\Component\Utils\Utils;
use Opit\Notes\CurrencyRateBundle\Model\ExchangeRateInterface;

/**
 * This abstract class is sharing common service methods for currency rate service classes.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage CurrencyRateBundle
 * @see ExchangeRateInterface
 */
abstract class AbstractExchangeRateService implements ExchangeRateInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Currency rates
     * @var mixin
     */
    protected $currencyRates;

    /**
     * Constructor
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function __construct(EntityManager $entityManager, Logger $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @internal
     */
    public function getExchangeRates($options = array())
    {
        // Check if the dates are exist in the options array.
        if (!isset($options['startDate'])) {
            $options['startDate'] = new \DateTime('today');
        }
        if (!isset($options['endDate'])) {
            $options['endDate'] = new \DateTime('today');
        }

        return $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->findAllByDates(
            $options['startDate'],
            $options['endDate']
        );
    }

    /**
     * @internal
     */
    public function getCurrentExchangeRates()
    {
       return $this->getExchangeRates();
    }

    /**
     * @internal
     */
    public function getRatesByDate(\DateTime $date = null)
    {
        // If datetime is null set to today
        if (null === $date) {
            $date = new \DateTime('today');
        }

        return $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->getRatesArray($date);
    }

    /**
     * @internal
     */
    public function getRateOfCurrency($code, \DateTime $datetime = null)
    {
        // If datetime is null set to today
        if (null === $datetime) {
             $datetime = new \DateTime('today');
        }
        $rate = $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')
            ->findRateByCodeAndDate(strtoupper($code), $datetime);

        // If rate is null throw an exception
        if (null === $rate) {
            throw new CommonException(sprintf('Rate entity not found for "%s, %s"', $code, $datetime->format('Y-m-d')));
        }

        return $rate->getRate();
    }

    /**
     * @internal
     */
    public function getLastLocalRateDate()
    {
        $rate =  $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->findLastRate();

        // If rate is null throw an exception
        if (null === $rate) {
            $this->logger->error(
                sprintf('[|%s] Rate entity not found. (Empty database)', Utils::getClassBasename($this))
            );
            throw new CommonException('Rate entity not found (empty database).');
        }

        return $rate->getCreated()->setTime(0, 0, 0);
    }

    /**
     * @internal
     */
    public function getFirstLocalRateDate()
    {
        $rate =  $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->findFirstRate();

        // If rate is null throw an exception
        if (null === $rate) {
            $this->logger->error(
                sprintf('[|%s] Rate entity not found. (Empty database)', Utils::getClassBasename($this))
            );
            throw new CommonException('Rate entity not found (empty database).');
        }

        return $rate->getCreated()->setTime(0, 0, 0);
    }

    /**
     * @internal
     */
    public function getDiffExchangeRates($options)
    {
        if (!isset($options['startDate']) || empty($options['startDate'])) {
            $startDate = $this->getFirstLocalRateDate();
            $options['startDate'] = $startDate->format('Y-m-d');
        }

        return $this->fetchExchangeRates($options);
    }

    /**
     * @internal
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

        return $this->fetchExchangeRates($options);
    }

    /**
     * @internal
     */
    public function setExchangeRates(array $exchangeRates)
    {
        $this->logger->info(sprintf('[|%s] Starting set exchange rates by manually.', Utils::getClassBasename($this)));
        $currencyRates = array();
        // Get the existing currency codes.
        $currencyCodes = $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')->getAllCurrencyCodes();
        foreach ($exchangeRates as $currencyCode => $dates) {

            // If the currency code exist then go forward else skip out it.
            if (in_array($currencyCode, $currencyCodes)) {
                // Iterate the dates
                foreach ($dates as $date => $value) {
                    // If date is valid then save it with the value else skip it.
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
     * @internal
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
    protected function setMissingRatesAndPersist(\DateTime $date, $days, $currencyCode, $value, $force)
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
    protected function createOrUpdateRate($currencyCode, $value, \DateTime $dateObj, $force)
    {
        if (!$this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')->hasRate($currencyCode, $dateObj)) {
            $rate = new Rate();
            $currency = $this->em->getRepository('OpitNotesCurrencyRateBundle:Currency')
                ->findOneByCode($currencyCode);
            $rate->setCurrencyCode($currency);
        } else {
            $rate = $this->em->getRepository('OpitNotesCurrencyRateBundle:Rate')
                ->findRateByCodeAndDate($currencyCode, $dateObj);

            // If this is not a force update or the rate's value wasn't changed skipt the update.
            if (!$force && $rate->getRate() === $value) {
                return null;
            }
        }
        $rate->setRate($value);
        $rate->setCreated($dateObj);
        $rate->setUpdated(new \DateTime('now'));

        return $rate;
    }
}
