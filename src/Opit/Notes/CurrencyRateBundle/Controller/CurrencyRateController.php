<?php

namespace Opit\Notes\CurrencyRateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * Returns exchange rates from MNB
     *
     * @Route("/secured/currencyrates/view", name="OpitNotesCurrencyRateBundle_currencyrates_view")
     * @Method({"GET"})
     * @Template()
     */
    public function getExchangeRatesAction(Request $request)
    {
        $options = $request->query->all();
        if (!isset($options['startDate'])) {
            $today = new \DateTime('today');
            $options['startDate'] = $today->format('Y-m-d');
        }
        
        $exch = $this->get('rate.exchange_service');
        $rates = $exch->getExchangeRates($options);
        
        return new JsonResponse($rates);
    }
    
    /**
     * To get covnerted rate of currency
     *
     * @Route("/secured/currencyrates/convert", name="OpitNotesCurrencyRateBundle_currencyrate_convert")
     * @Method({"GET"})
     * @Template()
     */
    public function getConvertedRateOfCurrencyAction(Request $request)
    {
        $originCode = $request->query->get('codeFrom');
        $destinationCode = $request->query->get('codeTo');
        $value = $request->query->get('value');
        
        $exch = $this->get('rate.exchange_service');
        $convertedValue = $exch->convertCurrency($originCode, $destinationCode, $value);
        
        return new JsonResponse(array($destinationCode => $convertedValue));
    }
}
