<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * This controller class is for the ChangeRateBundle.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage CurrencyRateBundle
 */
class CurrencyRateController extends Controller
{
    /**
     * Returns exchange rates from local database.
     *
     * @Route("/secured/currencyrates/view", name="OpitNotesCurrencyRateBundle_currencyrates_view")
     * @Method({"GET"})
     * @Template()
     */
    public function getExchangeRatesAction(Request $request)
    {
        $options = $request->query->all();

        if (!isset($options['startDate'])) {
            $options['startDate'] = new \DateTime('today');
        }

        // Call the concrete exchange rate service by alias.
        $exch = $this->get('opit.service.exchange_rates.default');
        $rates = $exch->getExchangeRates($options);

        $serializer = $this->container->get('jms_serializer');
        $data = $serializer->serialize($rates, 'json');
        
        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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

        // Call the concrete exchange rate service by alias.
        $exch = $this->get('opit.service.exchange_rates.default');
        $convertedValue = $exch->convertCurrency($originCode, $destinationCode, $value);

        return new JsonResponse(array($destinationCode => $convertedValue));
    }
}
