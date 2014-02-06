<?php

namespace Opit\Notes\CurrencyRateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This controller class is for the ChangeRateBundle.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage CurrrencyRateBundle
 */
class CurrencyRateController extends Controller
{

    /**
     * To get exchange rates
     *
     * @Route("/secured/currencyrates/view", name="OpitNotesCurrencyRateBundle_currencyrates_view")
     * @Template()
     */
    public function getExchangeRatesAction()
    {
        $exch = $this->get('opit.service.exchange_rates');
        $exch->getExchangeRates(array(
            //'startDate' => '2014-01-20',
            'endDate' => '2014-01-25',
            'currencyNames' => 'EUR,USD'
        ));
        $exch->saveExchangeRates(true);
        return new \Symfony\Component\HttpFoundation\Response();
    }
    
    /**
     * To get covnerted rate of currency
     *
     * @Route("/secured/currencyrates/convert", name="OpitNotesCurrencyRateBundle_currencyrate_convert")
     * @Template()
     */
    public function getConvertedRateOfCurrencyAction()
    {
        $request = $this->getRequest();
        $originCode = $request->request->get('originCode');
        $destinationCode = $request->request->get('destinationCode');
        $value = $request->request->get('value');

        $exch = $this->get('opit.service.exchange_rates');
        $convertedValue = $exch->convertCurrency($originCode, $destinationCode, $value);
        
        return new JsonResponse(array($destinationCode => $convertedValue));
    }
}
