<?php

namespace Opit\Notes\UserBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Description of SimpleIntegerToStringTransformer
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes/UserBundle
 */
class SimpleIntegerToStringTransformer implements DataTransformerInterface 
{
    
    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
       return (string) $value;
    }
    
    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }
        
        return (int) $value;
    }
}
