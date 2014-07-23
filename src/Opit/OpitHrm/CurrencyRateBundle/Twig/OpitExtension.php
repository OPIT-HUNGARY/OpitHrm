<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Twig;

use Opit\OpitHrm\CurrencyRateBundle\Model\ExchangeRateInterface;

/**
 * Twig OpitTravelExtension class
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 */
class OpitExtension extends \Twig_Extension
{
    /**
     * Exchange Rate service instance.
     * @var \Opit\OpitHrm\CurrencyRateBundle\Model\ExchangeRateInterface 
     */
    protected $rateService;

    /**
     * Constructor
     * @param \Opit\OpitHrm\CurrencyRateBundle\Model\ExchangeRateInterface $rateService
     */
    public function __construct(ExchangeRateInterface $rateService)
    {
        $this->rateService = $rateService;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'convertCurrency' => new \Twig_Function_Method($this, 'convertCurrency'),
        );
    }

    /**
     * Convert currency rate from the origin currency code to the destinaton currency code.
     *
     * @param string $originCode currency code
     * @param string $destinationCode currency code
     * @param integer $value value of the rate
     * @param string $dateTime
     * @return float
     */
    public function convertCurrency($originCode, $destinationCode, $value, $dateTime = null)
    {
        if (!($dateTime instanceof \DateTime)) {
            $dateTime = new \DateTime($dateTime);
        }

        $convertedValue = $this->rateService->convertCurrency($originCode, $destinationCode, $value, $dateTime);

        return $convertedValue;
    }

    /**
     * Get the extension's name.
     * @return string name of the extension
     */
    public function getName()
    {
        return 'opit_currency_extension';
    }
}
