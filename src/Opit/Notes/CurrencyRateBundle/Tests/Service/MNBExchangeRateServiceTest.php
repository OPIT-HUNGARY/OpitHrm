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

use Opit\Notes\CurrencyRateBundle\Service\MNBExchangeRateService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of ExchangeRateServiceTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class MNBExchangeRateServiceTest extends WebTestCase
{
    /**
     * @var \Opit\Notes\CurrencyRateBundle\Service\MNBExchangeRateService
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

        $this->exch = new MNBExchangeRateService($this->em, $this->logger, 1.0, $this->mnbUrl);
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
        $mockExch = $this->getMockBuilder('Opit\Notes\CurrencyRateBundle\Service\MNBExchangeRateService')
            ->disableOriginalConstructor()
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
     * testFetchCurrentExchangeRates
     */
    public function testFetchCurrentExchangeRates()
    {
        // Set up a mocked Exchange Service.
        $stubExch = $this->getMockBuilder('Opit\Notes\CurrencyRateBundle\Service\MNBExchangeRateService')
            ->setConstructorArgs(array($this->em, $this->logger, 1.0, $this->mnbUrl))
            ->setMethods(array('fetchExchangeRates'))
            ->getMock();
        // Configure the getExchangRates method.
        $stubExch->expects($this->any())
            ->method('fetchExchangeRates')
            ->will($this->returnValue(array('EUR' => array('2014-02-01' => '313.6'))));

        $resultArray = $stubExch->fetchCurrentExchangeRates();

        $this->assertTrue(is_array($resultArray), 'FetchCurrentExchangeRates: The result is not an array.');
        $this->assertArrayHasKey('EUR', $resultArray, 'FetchCurrentExchangeRates: missing currency code.');
        $this->assertArrayNotHasKey(
            'GBP',
            $resultArray,
            'FetchCurrentExchangeRates: GBP currency code key exists in the array.'
        );
        $this->assertEquals(
            313.6,
            $resultArray['EUR']['2014-02-01'],
            'FetchCurrentExchangeRates: The expected and given rate value are not equal.'
        );
    }

    /**
     * testFetchExchangeRates
     *
     * This method works with live data, which from the MNB's service
     */
    public function testFetchExchangeRates()
    {
        // Get the last week's friday.
        $lastFridayDate = date('Y-m-d', strtotime('last Friday'));

        $options = array(
            'startDate' => $lastFridayDate,
            'endDate' => $lastFridayDate,
            'currencyNames' => 'EUR,GBP,USD'
        );
        $response = $this->exch->fetchExchangeRates($options);

        $this->assertNotEmpty($response, 'FetchExchangeRates: empty response.');
        $this->assertArrayHasKey('EUR', $response, 'FetchExchangeRates: misssing currency code.');
        $this->assertArrayHasKey(
            $lastFridayDate,
            $response['EUR'],
            sprintf('FetchExchangeRates: The %s key does not exist in the response array.', $lastFridayDate)
        );
        $this->assertArrayNotHasKey(
            'CHF',
            $response,
            'FetchExchangeRates: CHF currency code is in the response array.'
        );
        $this->assertFalse($this->exch->fetchExchangeRates(array('startDate' => date('tomorrow'))));
        $this->assertFalse($this->exch->fetchExchangeRates(array('startDate' => '2014-02-30')));
        $this->assertFalse(
            $this->exch->fetchExchangeRates(array('startDate' => '2014-02-20', 'endDate' => '2014-02-31'))
        );
        $this->assertFalse(
            $this->exch->fetchExchangeRates(array('startDate' => '2014-02-20', 'currencyNames' => 'TFS8'))
        );
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    public function testFetchExchangeRatesMissingStartDateException()
    {
        $this->exch->fetchExchangeRates(array());
    }
}
