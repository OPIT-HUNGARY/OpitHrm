<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of LeaveCategoryType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class LeaveCategoryType extends AbstractType
{

     /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('attr' => array(
            'max_length' => 50,
            'placeholder' => 'Name'
        )));
        $builder->add('description', 'textarea', array('attr' => array(
            'max_length' => 255,
            'placeholder' => 'Description'
        )));
        $builder->add('leaveCategoryDuration', 'entity', array(
            'class' => 'OpitNotesLeaveBundle:LeaveCategoryDuration',
            'property' => 'leaveCategoryDurationName',
            'label' => 'Duration'
        ));
        $builder->add($builder->create('id', 'hidden', array('mapped' => false)));
    }

   /**
     * Sets the default form options
     *
     * @param object $resolver An OptionsResolver interface object
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\LeaveBundle\Entity\LeaveCategory',
        ));
    }
    /**
     * Get the name
     *
     * @return string name
     */
    public function getName()
    {
        return 'leaveCategory';
    }
}
