<?php

namespace Opit\Notes\TravelBundle\Model;

/**
 * The TravelCurrencyInterface responsible for which behaviours have to
 * implement for the Currency entity to be able to work with the Travel entities
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
interface TravelCurrencyInterface
{
    public function getCode();
}