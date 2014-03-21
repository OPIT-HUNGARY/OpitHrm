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
 * Description of RateRepositoryFunctionalTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class RateRepositoryFunctionalTest extends WebTestCase
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
     * test FindFirstRate method
     */
    public function testFindFirstRate()
    {
        $rate = $this->em->getRepository("OpitNotesCurrencyRateBundle:Rate")
                    ->findFirstRate();
        
        $this->assertNotNull($rate, 'FindFirstRate: The given result is null.');
    }
    
    /**
     * test FindLastRate method
     */
    public function testFindLastRate()
    {
        $rate = $this->em->getRepository("OpitNotesCurrencyRateBundle:Rate")
                    ->findLastRate();
        
        $this->assertNotNull($rate, 'FindLastRate: The given result is null.');
    }
    
    /**
     * test HasRate method
     */
    public function testHasRate()
    {
        $rate = $this->em->getRepository("OpitNotesCurrencyRateBundle:Rate")
                    ->hasRate('EUR', new \DateTime('today'));

        $this->assertTrue($rate, 'HasRate: The given result is null.');
    }
    
    /**
     * test FindRateByCodeAndDate method
     */
    public function testFindRateByCodeAndDate()
    {
        $rate = $this->em->getRepository("OpitNotesCurrencyRateBundle:Rate")
                    ->findRateByCodeAndDate('EUR', new \DateTime('today'));

        $this->assertNotNull($rate, 'FindRateByCodeAndDate: The given result is null.');
    }
    
    /**
     * test GetRatesArray
     */
    public function testGetRatesArray()
    {
        $rate = $this->em->getRepository("OpitNotesCurrencyRateBundle:Rate")
                    ->getRatesArray(new \DateTime('yesterday'));

        $this->assertNotNull($rate, 'GetRatesArray: The given result is null.');
    }
    
    /**
     * tear down
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
}
