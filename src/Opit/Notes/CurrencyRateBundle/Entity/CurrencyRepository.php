<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * This class is a repository for the Currency entity.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
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
