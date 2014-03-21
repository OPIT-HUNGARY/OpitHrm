<?php

namespace Opit\Notes\CurrencyRateBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * This class is a repository for the Currency entity.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage CurrencyRateBundle
 */
class CurrencyRepository extends EntityRepository
{
    /**
     * Get all currency codes from the database
     * @return array contains the currency codes
     */
    public function getAllCurrencyCodes()
    {
        $result = array();
        
        $currencies = $this->findAll();
        
        foreach ($currencies as $currency) {
            $result[] = $currency->getCode();
        }
        
        return $result;
    }
}
