<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Tests\Twig;

use Opit\OpitHrm\UserBundle\Twig\OpitExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of OpitExtensionTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class OpitExtensionTest extends WebTestCase
{
    /**
     * @var \Opit\OpitHrm\CurrencyRateBundle\Twig\OpitExtension
     */
    private $opitExtension;
    
    /**
     * set up the testing.
     */
    public function setUp()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));

        $this->opitExtension = new OpitExtension($client->getContainer());
    }
    
    /**
     * testing getName method.
     */
    public function testGetName()
    {
        $this->assertEquals(
            'opit_extension',
            $this->opitExtension->getName(),
            'testGetName: The expected and the given values are not equal.'
        );
    }
    
    /**
     * testing get globals method.
     */
    public function testGetGlobals()
    {
        $result = $this->opitExtension->getGlobals();
        
        $this->assertTrue(
            is_array($result),
            'testGetFunctions: The result is not an array.'
        );
        $this->assertArrayHasKey(
            'ldap_enabled',
            $result,
            'testGetGlobals: Missing ldap_enabled array key.'
        );
        $this->assertArrayHasKey(
            'security_roles',
            $result,
            'testGetGlobals: Missing security_roles array key.'
        );
    }
}
