<?php

/*
 * This file is part of the NOTES bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Tests\Twig;

use Opit\Notes\UserBundle\Twig\OpitExtension;

/**
 * Description of OpitExtensionTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class OpitExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Opit\Notes\CurrencyRateBundle\Twig\OpitExtension
     */
    private $opitExtension;
    
    /**
     * set up the testing.
     */
    public function setUp()
    {
        $this->opitExtension = new OpitExtension();
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
     * testing humanizeFilter method.
     */
    public function testHumanizeFilter()
    {
        $this->assertEquals(
            'Lower Case And Underscored Word',
            $this->opitExtension->humanizeFilter('lower_case_and_underscored_word'),
            'testHumanizeFilter: The string is not humanized.'
        );
    }
    
    /**
     * testing camelizeFilter method.
     */
    public function testCamelizeFilter()
    {
        $this->assertEquals(
            'LowerCaseAndUnderscoredWord',
            $this->opitExtension->camelizeFilter('lower_case_and_underscored_word'),
            'testCamelizeFilter: The string is not camelized.'
        );
    }
    
    /**
     * testing underscoreFilter method.
     */
    public function testUnderscoreFilter()
    {
        $this->assertEquals(
            '_lower_case_and_underscored_word',
            $this->opitExtension->underscoreFilter('LowerCaseAndUnderscoredWord'),
            'testUnderscoreFilter: The string is not underscored.'
        );
    }
    
    /**
     * testing strpos method.
     */
    public function testStrpos()
    {
        $this->assertEquals(
            '9',
            $this->opitExtension->strpos('LowerCaseAndUnderscoredWord', 'And'),
            'testUnderscoreFilter: The expected and the given index of the searched string are not equal.'
        );
    }
    
    /**
     * testing splitText method.
     */
    public function testSplitText()
    {
        $this->assertEquals(
            'Underscored',
            $this->opitExtension->splitText('Lower,Case,And,Underscored,Word', ',', 3),
            'testSplitText: The expected and the given splited text are not equal.'
        );
    }
    
    /**
     * testing getFunctions method.
     */
    public function testGetFunctions()
    {
        $result = $this->opitExtension->getFunctions();
        
        $this->assertTrue(
            is_array($result),
            'testGetFunctions: The result is not an array.'
        );
        $this->assertArrayHasKey(
            'strpos',
            $result,
            'testGetFunctions: Missing strpos array key.'
        );
        $this->assertArrayHasKey(
            'splitText',
            $result,
            'testGetFunctions: Missing splitText array key.'
        );
    }
    
    /**
     * testing getFilters method.
     */
    public function testGetFilters()
    {
        $result = $this->opitExtension->getFilters();
        
        $this->assertTrue(
            is_array($result),
            'testGetFilters: The result is not an array.'
        );
    }
}
