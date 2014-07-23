<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Tests\Service;

use Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of AbstractExchangeRateServiceTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 */
class AbstractExchangeRateServiceTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * Set up for the testing
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->logger = static::$kernel->getContainer()->get('logger');
    }

    /**
     * testGetCurrentExchangeRates
     */
    public function testGetCurrentExchangeRates()
    {
        // Set up a mocked Exchange Service.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($this->em, $this->logger))
            ->setMethods(array('getCurrentExchangeRates'))
            ->getMockForAbstractClass();
        // Configure the getExchangRates method.
        $stubExch->expects($this->any())
            ->method('getCurrentExchangeRates')
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
     * testGetExchangeRates
     */
    public function testGetExchangeRates()
    {
        // Get the last week's friday.
        $lastFridayDate = new \DateTime('last friday');
        // Set up the options array
        $options = array(
            'startDate' => $lastFridayDate,
            'endDate' => $lastFridayDate,
            'currencyNames' => 'EUR,GBP,USD'
        );

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($this->em, $this->logger))
            ->getMockForAbstractClass();
        $result = $stubExch->getExchangeRates($options);

        $this->assertNotEmpty($result, 'GetExchangeRates: empty response.');
        $this->assertTrue(is_array($result), 'GetExchangeRates: The result is not an array.');
    }

    /**
     * testGetRatesByDate
     */
    public function testGetRatesByDate()
    {
        // Create the first rate entity.
        $rate1 = $this->getMock('Opit\OpitHrm\CurrencyRateBundle\Entity\Rate');
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
        $rate2 = $this->getMock('Opit\OpitHrm\CurrencyRateBundle\Entity\Rate');
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

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($entityManager, $this->logger))
            ->setMethods(array('getRatesByDate'))
            ->getMockForAbstractClass();

        $stubExch->expects($this->any())
            ->method('getRatesByDate')
            ->will($this->returnValue(array($rate1, $rate2)));

        $result = $stubExch->getRatesByDate();
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
        $rate = $this->getMock('Opit\OpitHrm\CurrencyRateBundle\Entity\Rate');
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

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($entityManager, $this->logger))
            ->getMockForAbstractClass();

        $value = $stubExch->getRateOfCurrency('GBP', new \DateTime('2014-02-02 14:15:00'));

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

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($entityManager, $this->logger))
            ->getMockForAbstractClass();

        $stubExch->getRateOfCurrency('GBP');
    }

    /**
     * testGetLastLocalRateDate
     */
    public function testGetLastLocalRateDate()
    {
        // Create mocked rate entity.
        $rate = $this->getMock('Opit\OpitHrm\CurrencyRateBundle\Entity\Rate');
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

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($entityManager, $this->logger))
            ->getMockForAbstractClass();

        $dateOfLastRate = $stubExch->getLastLocalRateDate();

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

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($entityManager, $this->logger))
            ->getMockForAbstractClass();

        $stubExch->getLastLocalRateDate();
    }

    /**
     * testGetMissingExchangeRates
     */
    public function testGetMissingExchangeRates()
    {
        // Exchange ratesa for the fetchExchangeRates method.
        $exchangeRates = array(
            'CHF' => array(
                '2014-01-20' => 244.46,
                '2014-01-21' => 245.14,
            ),
            'EUR' => array(
                '2014-01-20' => 300.55,
                '2014-01-21' => 302.97,
            ),
        );
        // Set up the mocked currency repository
        $currencyRepository = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Entity\CurrencyRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $currencyRepository->expects($this->any())
            ->method('getAllCurrencyCodes')
            ->will($this->returnValue(array('EUR', 'CHF')));

        $entityManager = $this->getMockedEntityManager($currencyRepository);

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($entityManager, $this->logger))
            ->setMethods(array('getLastLocalRateDate'))
            ->getMockForAbstractClass();

        // Configure the getLastLocalRateDate() method for the exchange service stub.
        $stubExch->expects($this->at(0))
            ->method('getLastLocalRateDate')
            ->will($this->returnValue(new \DateTime('2014-01-20')));

        // Configure the fetchExchangeRates() method for the exchange service stub.
        $stubExch->expects($this->any())
            ->method('fetchExchangeRates')
            ->will($this->returnValue($exchangeRates));

        $response = $stubExch->getMissingExchangeRates(array());

        $this->assertNotEmpty($response, 'GetMissingExchangeRate: empty response.');
        $this->assertArrayHasKey('EUR', $response, 'GetMissingExchangeRate: missing currency code.');
        $this->assertArrayHasKey(
            '2014-01-20',
            $response['EUR'],
            sprintf('GetMissingExchangeRate: The %s key does not exist in the response array.', '2014-01-20')
        );
        $this->assertArrayNotHasKey(
            'GBP',
            $response,
            'GetMissingExchangeRate: GBP currency code is in the response array.'
        );
    }

    /**
     * testGetDiffExchangeRates
     */
    public function testGetDiffExchangeRates()
    {
        // Exchange ratesa for the fetchExchangeRates method.
        $exchangeRates = array(
            'USD' => array(
                '2014-01-20' => 244.46,
                '2014-01-21' => 245.14,
            ),
            'EUR' => array(
                '2014-01-20' => 300.55,
                '2014-01-21' => 302.97,
            ),
        );
        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($this->em, $this->logger))
            ->setMethods(array('getFirstLocalRateDate'))
            ->getMockForAbstractClass();

        // Configure the getLastLocalRateDate() method for the exchange service stub.
        $stubExch->expects($this->any())
            ->method('getFirstLocalRateDate')
            ->will($this->returnValue(new \DateTime('2014-01-21')));

        // Configure the fetchExchangeRates() method for the exchange service stub.
        $stubExch->expects($this->any())
            ->method('fetchExchangeRates')
            ->will($this->returnValue($exchangeRates));

        $response = $stubExch->getDiffExchangeRates(array());

        $this->assertNotEmpty($response, 'GetDiffExchangeRates: empty response.');
        $this->assertArrayHasKey('EUR', $response, 'GetDiffExchangeRates: missing currency code.');
        $this->assertArrayHasKey('USD', $response, 'GetDiffExchangeRates: missing currency code.');
        $this->assertArrayHasKey(
            '2014-01-21',
            $response['USD'],
            sprintf('GetDiffExchangeRates: The %s key does not exist in the response array.', '2014-01-21')
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

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($entityManager, $this->logger))
            ->getMockForAbstractClass();

        $stubExch->getFirstLocalRateDate();
    }

    public function testGetFirstLocalRateDate()
    {
        // Create mocked rate entity.
        $rate = $this->getMock('Opit\OpitHrm\CurrencyRateBundle\Entity\Rate');
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

        // Mocking the abstract exchange service class.
        $stubExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Service\AbstractExchangeRateService')
            ->setConstructorArgs(array($entityManager, $this->logger))
            ->getMockForAbstractClass();

        $stubExch->getFirstLocalRateDate();
        $dateOfFirstRate = $stubExch->getFirstLocalRateDate();

        $this->assertEquals(
            '2014-01-02 00:00:00',
            $dateOfFirstRate->format('Y-m-d H:i:s'),
            'GetFirstLocalRateDate: The expected date and the last local rate\'s date are not equal.'
        );
    }

    /**
     * Get a mocked Entity Manager
     *
     * @param Mock $repository object
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
        $rateRepository = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Entity\RateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        return $rateRepository;
    }
}
