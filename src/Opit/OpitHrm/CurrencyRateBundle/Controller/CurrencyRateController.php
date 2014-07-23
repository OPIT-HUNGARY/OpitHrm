<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;

/**
 * This rest controller class is for the ChangeRateBundle.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 */
class CurrencyRateController extends FOSRestController
{
    /**
     * Returns exchange rates from local database.
     *
     * @Rest\Get("secured/currency/exchange/rates.{_format}", name="OpitOpitHrmCurrencyRateBundle_api_currency_exchange_rates", requirements={"_format"="json|xml"}, defaults={"_format"="json"})
     * @Rest\QueryParam(name="startDate", nullable=true, requirements="[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])", description="Start date of rates")
     * @Rest\QueryParam(name="endDate", nullable=true, requirements="[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])", description="End date of rates")
     * @Rest\View()
     *
     * @return array.
     */
    public function getExchangeRatesAction(ParamFetcher $paramFetcher)
    {
        $options = $this->fetchParameters($paramFetcher);

        if (!isset($options['startDate'])) {
            $options['startDate'] = new \DateTime('today');
        }

        // Call the concrete exchange rate service by alias.
        $exch = $this->get('opit.service.exchange_rates.default');
        $rates = $exch->getExchangeRates($options);

        return array('rates' => $rates);
    }

    /**
     * To get covnerted rate of currency
     *
     * @Rest\Get("secured/currency/convert/rates.{_format}", name="OpitOpitHrmCurrencyRateBundle_api_curreny_convert_rates", requirements={"_format"="json|xml"}, defaults={"_format"="json"})
     * @Rest\QueryParam(name="codeFrom", strict=true, requirements="[a-zA-Z]{3}", description="The origin currency code")
     * @Rest\QueryParam(name="codeTo", strict=true, requirements="[a-zA-Z]{3}", description="The destination currency code")
     * @Rest\QueryParam(name="value", requirements="\d+", default="1", description="The value of origin rate")
     * @Rest\View()
     *
     * @return array
     */
    public function getConvertedRateOfCurrencyAction(ParamFetcher $paramFetcher)
    {
        $options = $this->fetchParameters($paramFetcher);

        // Call the concrete exchange rate service by alias.
        $exch = $this->get('opit.service.exchange_rates.default');
        $convertedValue = $exch->convertCurrency($options['codeFrom'], $options['codeTo'], $options['value']);

        return array($options['codeTo'] => $convertedValue);
    }

    /**
     * Fetch parameters from URI.
     *
     * @param \FOS\RestBundle\Request\ParamFetcher $paramFetcher
     * @return array of options
     */
    private function fetchParameters(ParamFetcher $paramFetcher)
    {
        $options = array();

        foreach ($paramFetcher->all() as $criterionName => $criterionValue) {
            // If the param is a date then create a datetime object
            if (false !== strpos(strtolower($criterionName), 'date')) {
                $options[$criterionName] = new \DateTime($criterionValue);
            } else {
                $options[$criterionName] = $criterionValue;
            }
        }

        return $options;
    }
}
