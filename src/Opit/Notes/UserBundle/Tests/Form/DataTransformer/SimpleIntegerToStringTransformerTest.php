<?php

namespace Opit\Notes\UserBundle\Form\DataTransformer;

use Opit\Notes\UserBundle\Form\DataTransformer\SimpleIntegerToStringTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Description of SimpleIntegerToStringTransformerTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class SimpleIntegerToStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * testTransform
     */
    public function testTransform()
    {
        $dataTransformer = new SimpleIntegerToStringTransformer();
        $result = $dataTransformer->transform(123456);
       
        $this->assertEquals(123456, $result, 'testTransform: The expected and the given values are not equal.');
        $this->assertInternalType('string', $result, 'testTransform: The result is not a string type.');
    }
    
    /**
     * testReverseTransform
     */
    public function testReverseTransform()
    {
        $dataTransformer = new SimpleIntegerToStringTransformer();
        $result = $dataTransformer->reverseTransform('123456');
        
        $this->assertEquals(123456, $result, 'testReverseTransform: The expected and the given values are not equal.');
        $this->assertInternalType('integer', $result, 'testReverseTransform: The result is not an integer type.');
    }
    
    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testReverseTransformException()
    {
        $dataTransformer = new SimpleIntegerToStringTransformer();
        $dataTransformer->reverseTransform(123456);
    }
}
