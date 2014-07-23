<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Tests\Twig;

use Opit\OpitHrm\CurrencyRateBundle\Twig\OpitExtension;

/**
 * Description of OpitExtensionTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 */
class OpitExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Opit\OpitHrm\CurrencyRateBundle\Twig\OpitExtension
     */
    private $opitExtension;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $mockExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Model\ExchangeRateInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->opitExtension = new OpitExtension($mockExch);
    }

    /**
     * testing convertCurrency method.
     */
    public function testConvertCurrency()
    {
        $mockExch = $this->getMockBuilder('Opit\OpitHrm\CurrencyRateBundle\Model\ExchangeRateInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('convertCurrency'))
            ->getMockForAbstractClass();

        $mockExch->expects($this->once())
            ->method('convertCurrency')
            ->will($this->returnValue(10));

        $opitExtension = new OpitExtension($mockExch);
        
        $this->assertEquals(
            10,
            $opitExtension->convertCurrency('EUR', 'GBP', 12),
            'ConvertCurrency: The expected and the given values are not equal.'
        );
    }

    /**
     * testing getName method.
     */
    public function testGetName()
    {
        $this->assertEquals(
            'opit_currency_extension',
            $this->opitExtension->getName(),
            'GetName: The expected and the given values are not equal.'
        );
    }

    /**
     * testing getName method.
     */
    public function testFunctions()
    {
        $this->assertTrue(
            is_array($this->opitExtension->getFunctions()),
            'GetFunctions: The given result is not an array.'
        );
        $this->assertArrayHasKey(
            'convertCurrency',
            $this->opitExtension->getFunctions(),
            'GetFunctions: Missing array key.'
        );
    }
}
