<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\UserBundle\Entity\Employee;

/**
 * Description of EmployeeIdToObjectTransformer
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class EmployeeIdToObjectTransformer implements DataTransformerInterface
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
    
    /**
     * 
     * @param int $number
     * @return null
     */
    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }
        
        $object = $this->objectManager
            ->getRepository('OpitOpitHrmUserBundle:Employee')
            ->find($number);
        
        return $object;
    }
    
    /**
     * 
     * @param Employee $object
     * @return string
     */
    public function transform($object)
    {
        if (null === $object) {
            return '';
        }
        
        return $object->getId();
    }
}
