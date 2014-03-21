<?php

/*
 * This file is part of the NOTES bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of CurrencyRateControllerTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class CurrencyRateControllerTest extends WebTestCase
{
    protected $client;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
    }
    
    /**
     * test GetConvertedRateOfCurrency action
     */
    public function testGetConvertedRateOfCurrencyAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/currencyrates/convert',
            array('codeFrom' => 'EUR', 'codeTo' => 'HUF', 'value' => '1', )
        );
        $content = $this->client->getResponse()->getContent();
        $decodedJson = json_decode($content, true);

        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            'GetConvertedRateOfCurrencyAction: Retrieved response failed.'
        );
        $this->assertJson($content, 'GetConvertedRateOfCurrencyAction: The content is not a JSON object.');
        $this->assertTrue(
            array_key_exists('HUF', $decodedJson),
            'GetConvertedRateOfCurrencyAction: Missing array key "HUF".'
        );
    }
    
    /**
     * test GetExchangeRates action
     */
    public function testGetExchangeRatesAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/currencyrates/view'
        );
        $content = $this->client->getResponse()->getContent();

        $decodedJson = json_decode($content, true);
        
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            'GetExchangeRatesAction: Retrieved response failed.'
        );
        $this->assertJson($content, 'GetExchangeRatesAction: The content is not a JSON object.');
        $this->assertTrue(
            array_key_exists('EUR', $decodedJson),
            'GetExchangeRatesAction: Missing array key "EUR".'
        );
    }
}
