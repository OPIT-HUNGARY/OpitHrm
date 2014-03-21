<?php

/*
 * This file is part of the NOTES bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Tests\Helper;

use Opit\Notes\CurrencyRateBundle\Helper\Utils;

/**
 * Description of UtilsTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testing getClassBasename method.
     */
    public function testGetClassBasename()
    {
        $utils = new Utils();
        $result = Utils::getClassBasename($utils);
        
        $this->assertEquals('Utils', $result, 'Getting class\'s name.');
    }
    
    /**
     * testing validateDate method.
     */
    public function testValidateDate()
    {
        $this->assertTrue(Utils::validateDate(date('2014-01-01'), 'Y-m-d'), 'ValidateDate: invalid date.');
        $this->assertTrue(Utils::validateDate(date('2014-01-01 15:34:56')), 'ValidateDate: invalid datetime.');
        $this->assertFalse(Utils::validateDate(date('2014-02-30'), 'Y-m-d'), 'ValidateDate: valid date.');
        $this->assertFalse(Utils::validateDate(date('2014-02-30 25:02:78')), 'ValidateDate valid datetime.');
        $this->assertTrue(
            Utils::validateDate(date('2014/03/10 12:35:10'), 'Y/m/d H:i:s'),
            'ValidateDate: The given datetime and its format are not covenant.'
        );
        $this->assertFalse(
            Utils::validateDate(date('2014/03/10'), 'Y-m-d H:i:s'),
            'ValidateDate: The given date and its format are covenant.'
        );
    }
    
    /**
     * testing validateCurrencyCodeString method.
     */
    public function testValidateCurrencyCodesString()
    {
        $this->assertTrue(
            Utils::validateCurrencyCodesString('HUF'),
            'ValidateCurrencyCodesString: HUF is invalid currency code.'
        );
        $this->assertTrue(
            Utils::validateCurrencyCodesString('HUF,EUR'),
            'ValidateCurrencyCodesString: HUF,EUR are invalid currency codes.'
        );
        $this->assertFalse(
            Utils::validateCurrencyCodesString('HUFMuf'),
            'ValidateCurrencyCodesString: HUFMuf is valid currency code.'
        );
        $this->assertFalse(
            Utils::validateCurrencyCodesString('HU2'),
            'ValidateCurrencyCodesString: HU2 is valid currency code.'
        );
        $this->assertFalse(
            Utils::validateCurrencyCodesString('HUF|EUR'),
            'ValidateCurrencyCodesString: "|" is valid currency code seperator character.'
        );
    }
}
