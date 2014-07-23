<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of CurrencyRateControllerTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
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
        // Testing JSON response.
        $crawler = $this->client->request(
            'GET',
            '/secured/currency/convert/rates.json',
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

        // Testing XML response.
        $crawler = $this->client->request(
            'GET',
            '/secured/currency/convert/rates.xml',
            array('codeFrom' => 'EUR', 'codeTo' => 'HUF', 'value' => '1', )
        );
        $responseXML = $this->client->getResponse();

        $this->assertTrue(
            $responseXML->headers->contains('Content-Type', 'text/xml; charset=UTF-8'),
            'GetConvertedRateOfCurrencyAction: The content-type is not a xml.'
        );
    }

    /**
     * test GetExchangeRates action
     */
    public function testGetExchangeRatesAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/currency/exchange/rates'
        );
        $content = $this->client->getResponse()->getContent();

        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            'GetExchangeRatesAction: Retrieved response failed.'
        );
        $this->assertJson($content, 'GetExchangeRatesAction: The content is not a JSON object.');

        // Testing XML response.
        $crawler = $this->client->request(
            'GET',
            '/secured/currency/exchange/rates.xml'
        );
        $responseXML = $this->client->getResponse();

        $this->assertTrue(
            $responseXML->headers->contains('Content-Type', 'text/xml; charset=UTF-8'),
            'GetExchangeRatesAction: The content-type is not a xml.'
        );
    }
}
