<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Acme\TaskBundle\Entity\Issue;

/**
 * Description of UserIdToObjectTransformer
 *
 * @author OPIT\Notes
 */
class UserIdToObjectTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    
    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }

        $object = $this->objectManager
            ->getRepository('OpitNotesUserBundle:User')
            ->find(array('id' => $number));

        if (null === $object) {
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $number
            ));
        }

        return $object;
    }
    
    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $number
     *
     * @return Issue|null
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function transform($object)
    {
        if (null === $object) {
            return '';
        }
        
        return $object->getId();
    }
}
