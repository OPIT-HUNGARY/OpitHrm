<?php

namespace Opit\Notes\CurrencyRateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\CurrencyRateBundle\Entity\Currency;
use Opit\Notes\CurrencyRateBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of PerDiemFixtures
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage CurrencyRateBundle
 */
class CurrencyFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $codes = array(
            'CHF' => 'Swiss Franc',
            'EUR' => 'Euro',
            'HUF' => 'Hungarian Forint',
            'GBP' => 'Pound Sterling',
            'USD' => 'US Dollar'
        );

        foreach ($codes as $key => $value) {
            $currency = new Currency();
            $currency->setCode($key);
            $currency->setDescription($value);
            $manager->persist($currency);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10; // the order in which fixtures will be loaded
    }
    
    /**
     * 
     * @return array
     */
    protected function getEnvironments()
    {
        return array('dev', 'prod');
    }
}
