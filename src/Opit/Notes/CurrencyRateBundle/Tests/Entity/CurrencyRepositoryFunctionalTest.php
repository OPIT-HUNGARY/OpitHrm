<?php

/*
 * This file is part of the NOTES bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of CurrencyRepositoryFunctionalTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class CurrencyRepositoryFunctionalTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Set up the testing
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
    
    /**
     * test GetAllCurrencyCodes method
     */
    public function testGetAllCurrencyCodes()
    {
        $currencies = $this->em->getRepository("OpitNotesCurrencyRateBundle:Currency")
                    ->getAllCurrencyCodes();
        
        $this->assertTrue(is_array($currencies), 'GetAllCurrencyCodes: It is not an array.');
        $this->assertTrue(in_array('HUF', $currencies), 'GetAllCurrencyCode: Missing HUF array key.');
        $this->assertTrue(in_array('EUR', $currencies), 'GetAllCurrencyCodes: Missing EUR array key.');
        $this->assertEquals(
            array('CHF', 'EUR', 'GBP', 'HUF', 'USD'),
            $currencies,
            'GetAllCurrencyCodes: The expected and the given results are not equal.'
        );
    }
    
    /**
     * tear down
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
}
