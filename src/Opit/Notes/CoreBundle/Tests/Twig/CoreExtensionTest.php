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

use Opit\Notes\CoreBundle\Twig\CoreExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of CoreExtensionTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class CoreExtensionTest extends WebTestCase
{
    /**
     * @var \Opit\Notes\CoreBundle\Twig\CoreExtension
     */
    private $coreExtension;

    /**
     * set up the testing.
     */
    public function setUp()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));

        $this->coreExtension = new CoreExtension($client->getContainer());
    }

    /**
     * testing getName method.
     */
    public function testGetName()
    {
        $this->assertEquals(
            'core_extension',
            $this->coreExtension->getName(),
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
            $this->coreExtension->humanizeFilter('lower_case_and_underscored_word'),
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
            $this->coreExtension->camelizeFilter('lower_case_and_underscored_word'),
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
            $this->coreExtension->underscoreFilter('LowerCaseAndUnderscoredWord'),
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
            $this->coreExtension->strpos('LowerCaseAndUnderscoredWord', 'And'),
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
            $this->coreExtension->splitText('Lower,Case,And,Underscored,Word', ',', 3),
            'testSplitText: The expected and the given splited text are not equal.'
        );
    }
}
