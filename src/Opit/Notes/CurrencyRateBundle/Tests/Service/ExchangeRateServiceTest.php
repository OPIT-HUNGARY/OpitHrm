<?php

/*
 * This file is part of the NOTES bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Tests\Service;

use Opit\Notes\CurrencyRateBundle\Service\ExchangeRateService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of ExchangeRateServiceTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class ExchangeRateServiceTest extends WebTestCase
{
    /**
     * @var \Opit\Notes\CurrencyRateBundle\Service\ExchangeRateService
     */
    private $exch;
    
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    
    /**
     * @var Symfony\Bridge\Monolog\Logger
     */
    private $logger;
    
    /**
     * @var string
     */
    private $mnbUrl;
    
    /**
     * Set up for the testing
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->logger = static::$kernel->getContainer()->get('logger');
        $this->mnbUrl = 'http://www.mnb.hu/arfolyamok.asmx?wsdl';
        
        $this->exch = new ExchangeRateService($this->em, $this->logger, 1.0, $this->mnbUrl);
    }
    
    /**
     * testGetHufRate
     */
    public function testGetHufRate()
    {
        $this->assertEquals(1.0, $this->exch->getHufRate(), 'GetHufRate: The rate of HUF is not (float) 1.0 .');
    }
    
    /**
     * testConvertCurrency
     */
    public function testConvertCurrency()
    {
        $mockExch = $this->getMockBuilder('Opit\Notes\CurrencyRateBundle\Service\ExchangeRateService')
            ->setConstructorArgs(array($this->em, $this->logger, 1.0, $this->mnbUrl))
            ->setMethods(array('getRateOfCurrency', 'getHufRate'))
            ->getMock();
        
        $mockExch->expects($this->at(0))
            ->method('getRateOfCurrency')
            ->with('GBP', new \DateTime('2014-01-05'))
            ->will($this->returnValue(360.0));
        
        $mockExch->expects($this->at(1))
            ->method('getRateOfCurrency')
            ->with('EUR', new \DateTime('2014-01-05'))
            ->will($this->returnValue(300.0));
        
        $mockExch->expects($this->at(2))
            ->method('getHufRate')
            ->will($this->returnValue(1.0));
        
        $mockExch->expects($this->at(3))
            ->method('getRateOfCurrency')
            ->with('EUR', new \DateTime('2014-01-05'))
            ->will($this->returnValue(300.0));
 
        $this->assertEquals(
            10,
            $mockExch->convertCurrency('EUR', 'GBP', 12, new \DateTime('2014-01-05')),
            'ConvertCurrency: The expected and the converted values are not equal.'
        );
        $this->assertEquals(
            12,
            $mockExch->convertCurrency('EUR', 'EUR', 12, new \DateTime('2014-01-05')),
            'ConvertCurrency: The expected and the converted values are not equal.'
        );
        $this->assertEquals(
            3600,
            $mockExch->convertCurrency('EUR', 'HUF', 12, new \DateTime('2014-01-05')),
            'ConvertCurrency: The expected and the converted values are not equal.'
        );
    }
    
    /**
     * testGetCurrentExchangeRates
     */
    public function testGetCurrentExchangeRates()
    {
        // Set up a mocked Exchange Service.
        $stubExch = $this->getMockBuilder('Opit\Notes\CurrencyRateBundle\Service\ExchangeRateService')
            ->setConstructorArgs(array($this->em, $this->logger, 1.0, $this->mnbUrl))
            ->setMethods(array('getExchangeRates'))
            ->getMock();
        // Configure the getExchangRates method.
        $stubExch->expects($this->any())
            ->method('getExchangeRates')
            ->will($this->returnValue(array('EUR' => array('2014-02-01' => '313.6'))));
        
        $resultArray = $stubExch->getCurrentExchangeRates();
        
        $this->assertTrue(is_array($resultArray), 'GetCurrentExchangeRates: The result is not an array.');
        $this->assertArrayHasKey('EUR', $resultArray, 'GetCurrentExchangeRates: missing currency code.');
        $this->assertArrayNotHasKey(
            'GBP',
            $resultArray,
            'GetCurrentExchangeRates: GBP currency code key exists in the array.'
        );
        $this->assertEquals(
            313.6,
            $resultArray['EUR']['2014-02-01'],
            'GetCurrentExchangeRates: The expected and given rate value are not equal.'
        );
    }
    
    /**
     * testGetRatesByDate
     */
    public function testGetRatesByDate()
    {
        // Create the first rate entity.
        $rate1 = $this->getMock('Opit\Notes\CurrencyRateBundle\Entity\Rate');
        $rate1->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $rate1->expects($this->any())
            ->method('getCurrencyCode')
            ->will($this->returnValue('EUR'));
        $rate1->expects($this->any())
            ->method('getRate')
            ->will($this->returnValue(302.2));
        // Create the second rate entity.
        $rate2 = $this->getMock('Opit\Notes\CurrencyRateBundle\Entity\Rate');
        $rate2->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));
        $rate2->expects($this->any())
            ->method('getCurrencyCode')
            ->will($this->returnValue('USD'));
        $rate2->expects($this->any())
            ->method('getRate')
            ->will($this->returnValue(208.4));
        
        // Set up the mocked rate repository.
        $rateRepository = $this->getMockedRateRepository();
        $rateRepository->expects($this->any())
            ->method('getRatesArray')
            ->will($this->returnValue(array($rate1, $rate2)));
        // Set up the mocked entity manager.
        $entityManager = $this->getMockedEntityManager($rateRepository);
        
        $exch = new ExchangeRateService($entityManager, $this->logger, 1.0, $this->mnbUrl);
        $result = $exch->getRatesByDate();
        $resultRate1 = $result[0];
        $resultRate2 = $result[1];
        
        $this->assertTrue(is_array($result), 'GetRatesByDate: The result is not an array.');
        $this->assertTrue(2 === count($result), 'GetRatesByDate: The number of the elements is not equals to 2.');
        $this->assertEquals(
            'EUR',
            $resultRate1->getCurrencyCode(),
            'GetRatesByDate: The first rate\'s currency code is not EUR.'
        );
        $this->assertEquals(
            208.4,
            $resultRate2->getRate(),
            'GetRatesByDate: The second rate\'s value is not equals to the expected value.'
        );
    }
    
    /**
     * testGetRateOfCurrency
     */
    public function testGetRateOfCurrency()
    {
        // Create mocked rate entity.
        $rate = $this->getMock('Opit\Notes\CurrencyRateBundle\Entity\Rate');
        $rate->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(45));
        $rate->expects($this->any())
            ->method('getCurrencyCode')
            ->will($this->returnValue('GBP'));
        $rate->expects($this->any())
            ->method('getRate')
            ->will($this->returnValue(354.2));
        $rate->expects($this->any())
            ->method('getCreated')
            ->will($this->returnValue('2014-02-02 14:15:00'));
        
        // Set up the mocked rate repository
        $rateRepository = $this->getMockedRateRepository();
        $rateRepository->expects($this->any())
            ->method('findRateByCodeAndDate')
            ->will($this->returnValue($rate));
        // Set up the mocked entity manager.
        $entityManager = $this->getMockedEntityManager($rateRepository);
        
        $exch = new ExchangeRateService($entityManager, $this->logger, 1.0, $this->mnbUrl);
        $value = $exch->getRateOfCurrency('GBP', new \DateTime('2014-02-02 14:15:00'));
        
        $this->assertEquals(354.2, $value, 'GetRateOfCurrency: The expected and the given values are not equal.');
        $this->assertNotNull($value, 'GetRateOfCurrency: The given value is null.');
    }
    
    /**
     * @expectedException \Doctrine\Common\CommonException
     */
    public function testGetRateOfCurrencyException()
    {
        // Set up the mocked rate repository
        $rateRepository = $this->getMockedRateRepository();
        $rateRepository->expects($this->any())
            ->method('findRateByCodeAndDate')
            ->will($this->returnValue(null));
        // Set up the mocked entity manager.
        $entityManager = $this->getMockedEntityManager($rateRepository);
        
        $exch = new ExchangeRateService($entityManager, $this->logger, 1.0, $this->mnbUrl);
        $exch->getRateOfCurrency('GBP');
    }
    
    /**
     * testGetLastLocalRateDate
     */
    public function testGetLastLocalRateDate()
    {
        // Create mocked rate entity.
        $rate = $this->getMock('Opit\Notes\CurrencyRateBundle\Entity\Rate');
        $rate->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(234));
        $rate->expects($this->any())
            ->method('getCurrencyCode')
            ->will($this->returnValue('EUR'));
        $rate->expects($this->any())
            ->method('getRate')
            ->will($this->returnValue(300.2));
        $rate->expects($this->any())
            ->method('getCreated')
            ->will($this->returnValue(new \DateTime('2014-12-02 14:36:32')));
        
        // Set up the mocked rate repository
        $rateRepository = $this->getMockedRateRepository();
        $rateRepository->expects($this->any())
            ->method('findLastRate')
            ->will($this->returnValue($rate));
        // Set up the mocked entity manager.
        $entityManager = $this->getMockedEntityManager($rateRepository);
        
        $exch = new ExchangeRateService($entityManager, $this->logger, 1.0, $this->mnbUrl);
        $dateOfLastRate = $exch->getLastLocalRateDate();
        
        $this->assertEquals(
            '2014-12-02 00:00:00',
            $dateOfLastRate->format('Y-m-d H:i:s'),
            'GetLastLocalRateDate: The expected date and the last local rate\'s date are not equal.'
        );
    }
    
    /**
     * @expectedException \Doctrine\Common\CommonException
     */
    public function testGetLastLocalRateDateException()
    {
        // Set up the mocked rate repository
        $rateRepository = $this->getMockedRateRepository();
        $rateRepository->expects($this->any())
            ->method('findLastRate')
            ->will($this->returnValue(null));
        // Set up the mocked entity manager.
        $entityManager = $this->getMockedEntityManager($rateRepository);
        
        $exch = new ExchangeRateService($entityManager, $this->logger, 1.0, $this->mnbUrl);
        $exch->getLastLocalRateDate();
    }
    
    /**
     * testGetExchangeRates
     * 
     * This method works with live data, which from the MNB's service
     */
    public function testGetExchangeRates()
    {
        // Get the last week's friday.
        $lastFridayDate = date('Y-m-d', strtotime('last Friday'));
        
        $options = array(
            'startDate' => $lastFridayDate,
            'endDate' => $lastFridayDate,
            'currencyNames' => 'EUR,GBP,USD'
        );
        $response = $this->exch->getExchangeRates($options);

        $this->assertNotEmpty($response, 'GetExchangeRates: empty response.');
        $this->assertArrayHasKey('EUR', $response, 'GetExchangeRates: misssing currency code.');
        $this->assertArrayHasKey(
            $lastFridayDate,
            $response['EUR'],
            sprintf('GetExchangeRates: The %s key does not exist in the response array.', $lastFridayDate)
        );
        $this->assertArrayNotHasKey('CHF', $response, 'GetExchangeRates: CHF currency code is in the response array.');
        $this->assertFalse($this->exch->getExchangeRates(array('startDate' => date('tomorrow'))));
        $this->assertFalse($this->exch->getExchangeRates(array('startDate' => '2014-02-30')));
        $this->assertFalse(
            $this->exch->getExchangeRates(array('startDate' => '2014-02-20', 'endDate' => '2014-02-31'))
        );
        $this->assertFalse(
            $this->exch->getExchangeRates(array('startDate' => '2014-02-20', 'currencyNames' => 'TFS8'))
        );
    }
    
    /**
     * @expectedException \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    public function testGetExchangeRatesMissingStartDateException()
    {
        // Set up the mocked rate repository
        $rateRepository = $this->getMockedRateRepository();
        $rateRepository->expects($this->any())
            ->method('findRateByCodeAndDate')
            ->will($this->returnValue(null));
        // Set up the mocked entity manager.
        $entityManager = $this->getMockedEntityManager($rateRepository);
        
        $exch = new ExchangeRateService($entityManager, $this->logger, 1.0, $this->mnbUrl);
        $exch->getExchangeRates(array());
    }
    
    /**
     * testGetMissingExchangeRates
     */
    public function testGetMissingExchangeRates()
    {
        // Get the last week's thursday.
        $lastThursdayDate = date('Y-m-d', strtotime('last Thursday'));
      
        // Set up the mocked currency repository
        $currencyRepository = $this->getMockBuilder('Opit\Notes\CurrencyRateBundle\Entity\CurrencyRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $currencyRepository->expects($this->any())
            ->method('getAllCurrencyCodes')
            ->will($this->returnValue(array('EUR', 'CHF')));
        
        $entityManager = $this->getMockedEntityManager($currencyRepository);

        // Create a mocked Exchange Service.
        $stubExch = $this->getMock(
            'Opit\Notes\CurrencyRateBundle\Service\ExchangeRateService',
            array('getLastLocalRateDate'),
            array($entityManager, $this->logger, 1.0, $this->mnbUrl)
        );
        // Configure the getLastLocalRateDate() method for the exchange service stub.
        $stubExch->expects($this->at(0))
            ->method('getLastLocalRateDate')
            ->will($this->returnValue(new \DateTime($lastThursdayDate)));

        $response = $stubExch->getMissingExchangeRates(array());
        
        $this->assertNotEmpty($response, 'GetMissingExchangeRate: empty response.');
        $this->assertArrayHasKey('EUR', $response, 'GetMissingExchangeRate: missing currency code.');
        $this->assertArrayHasKey(
            $lastThursdayDate,
            $response['EUR'],
            sprintf('GetMissingExchangeRate: The %s key does not exist in the response array.', $lastThursdayDate)
        );
        $this->assertArrayNotHasKey(
            'GBP',
            $response,
            'GetMissingExchangeRate: GBP currency code is in the response array.'
        );
       
        $stubExch2 = $this->getMock(
            'Opit\Notes\CurrencyRateBundle\Service\ExchangeRateService',
            array('getLastLocalRateDate'),
            array($entityManager, $this->logger, 1.0, $this->mnbUrl)
        );
        $stubExch2->expects($this->at(0))
            ->method('getLastLocalRateDate')
            ->will($this->returnValue(new \DateTime('today')));
        
        $noMissingRates = $stubExch2->getMissingExchangeRates(array());
        
        $this->assertFalse($noMissingRates);
    }
    
    /**
     * testGetDiffExchangeRates
     */
    public function testGetDiffExchangeRates()
    {
        // Get the last week's Monday.
        $lastMondayDate = date('Y-m-d', strtotime('last Monday'));
        
        // Create a mocked Exchange Service.
        $stubExch = $this->getMock(
            'Opit\Notes\CurrencyRateBundle\Service\ExchangeRateService',
            array('getFirstLocalRateDate'),
            array($this->em, $this->logger, 1.0, $this->mnbUrl)
        );
        // Configure the getLastLocalRateDate() method for the exchange service stub.
        $stubExch->expects($this->any())
            ->method('getFirstLocalRateDate')
            ->will($this->returnValue(new \DateTime($lastMondayDate)));
        
        $response = $stubExch->getDiffExchangeRates(array());
        
        $this->assertNotEmpty($response, 'GetDiffExchangeRates: empty response.');
        $this->assertArrayHasKey('EUR', $response, 'GetDiffExchangeRates: missing currency code.');
        $this->assertArrayHasKey('USD', $response, 'GetDiffExchangeRates: missing currency code.');
        $this->assertArrayHasKey(
            $lastMondayDate,
            $response['USD'],
            sprintf('GetDiffExchangeRates: The %s key does not exist in the response array.', $lastMondayDate)
        );
    }
    
    /**
     * @expectedException \Doctrine\Common\CommonException
     */
    public function testGetFirstLocalRateDateException()
    {
        // Set up the mocked rate repository
        $rateRepository = $this->getMockedRateRepository();
        $rateRepository->expects($this->any())
            ->method('findFirstRate')
            ->will($this->returnValue(null));
        // Set up the mocked entity manager.
        $entityManager = $this->getMockedEntityManager($rateRepository);
        
        $exch = new ExchangeRateService($entityManager, $this->logger, 1.0, $this->mnbUrl);
        $exch->getFirstLocalRateDate();
    }
    
    public function testGetFirstLocalRateDate()
    {
        // Create mocked rate entity.
        $rate = $this->getMock('Opit\Notes\CurrencyRateBundle\Entity\Rate');
        $rate->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $rate->expects($this->any())
            ->method('getCurrencyCode')
            ->will($this->returnValue('EUR'));
        $rate->expects($this->any())
            ->method('getRate')
            ->will($this->returnValue(290.2));
        $rate->expects($this->any())
            ->method('getCreated')
            ->will($this->returnValue(new \DateTime('2014-01-02 14:12:02')));
        
        // Set up the mocked rate repository
        $rateRepository = $this->getMockedRateRepository();
        $rateRepository->expects($this->any())
            ->method('findFirstRate')
            ->will($this->returnValue($rate));
        // Set up the mocked entity manager.
        $entityManager = $this->getMockedEntityManager($rateRepository);
        
        $exch = new ExchangeRateService($entityManager, $this->logger, 1.0, $this->mnbUrl);
        $dateOfFirstRate = $exch->getFirstLocalRateDate();
        
        $this->assertEquals(
            '2014-01-02 00:00:00',
            $dateOfFirstRate->format('Y-m-d H:i:s'),
            'GetFirstLocalRateDate: The expected date and the last local rate\'s date are not equal.'
        );
    }
    
    /**
     * Get a mocked Entity Manager
     * 
     * @param Mock $rateRepository object
     * @return Mock $entityManager
     */
    private function getMockedEntityManager($repository)
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));
        
        return $entityManager;
    }
    
    /**
     * Get a mocked RateRepository object.
     * 
     * @return Mock $rateRepository
     */
    private function getMockedRateRepository()
    {
        $rateRepository = $this->getMockBuilder('Opit\Notes\CurrencyRateBundle\Entity\RateRepository')
            ->disableOriginalConstructor()
            ->getMock();
        
        return $rateRepository;
    }
}
