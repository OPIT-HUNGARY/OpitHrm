<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of EmployeeType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class EmployeeType extends AbstractType
{
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('employeeName', 'text', array('attr' => array(
            'placeholder' => 'Employee Name'
        )));
        
        $builder->add('teams', 'entity', array(
            'label' => 'Teams',
            'class' => 'OpitNotesUserBundle:Team',
            'property' => 'teamName',
            'required' => false,
            'multiple' => true,
            'expanded' => true
        ));
        
        $builder->add('numberOfChildren', 'integer', array(
            'label' => 'No. Of Children (< 30)',
            'invalid_message' => 'No. of children can only contain integer values.',
            'attr' => array(
                'min' => 0,
                'max' => 30,
                'placeholder' => 'Number of children'
            )
        ));
        
        $builder->add('joiningDate', 'date', array(
            'widget' => 'single_text',
            'attr' => array(
                'placeholder' => 'Joining date'
            )
        ));
        
        $builder->add('dateOfBirth', 'date', array(
            'widget' => 'single_text',
            'attr' => array(
                'placeholder' => 'Date of birth'
            )
        ));

        $builder->add('workingHours', 'integer', array(
            'invalid_message' => 'Working hours can only contain integer values.',
            'attr' => array(
                'min' => 0,
                'max' => 24,
                'placeholder' => 'Working hours'
            )
        ));
    }
    
    /**
     * Sets the default form options
     * 
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\UserBundle\Entity\Employee'
        ));
    }

    /**
     * Get name
     * 
     * @return string
     */
    public function getName()
    {
        return 'employee';
    }
}
