<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of DefaultControllerTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\BrowserKit\Client 
     */
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
     * test index action
     */
    public function testIndexAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testIndexAction: The content-type is not a html file.'
        );
    }
    
    /**
     * test version action
     */
    public function testVersionAction()
    {
        
        $crawler = $this->client->request(
            'GET',
            '/secured/opithrm/versions'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testVersionAction: The content-type is not a html file.'
        );
    }
}
