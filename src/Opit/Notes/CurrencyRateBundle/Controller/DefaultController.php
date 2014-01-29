<?php

namespace Opit\Notes\CurrencyRateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * This controller class is for the ChangeRateBundle.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage CurrrencyRateBundle
 */
class DefaultController extends Controller
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
        $exch->getCurrentExchangeRates();
        $exch->saveExchangeRates();
        return new \Symfony\Component\HttpFoundation\Response();
    }
}
