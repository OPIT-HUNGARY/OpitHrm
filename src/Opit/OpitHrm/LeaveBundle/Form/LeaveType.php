<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Description of LeaveType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveType extends AbstractType
{
     /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', 'date', array(
            'widget' => 'single_text',
            'label' => 'Start date',
            'error_bubbling' => true,
            'attr' => array(
                'placeholder' => 'Start date',
                'class' => 'start-date'
            )
        ));

        $builder->add('endDate', 'date', array(
            'widget' => 'single_text',
            'label' => 'End date',
            'error_bubbling' => true,
            'attr' => array(
                'placeholder' => 'End date',
                'class' => 'end-date'
            )
        ));

        $builder->add('description', 'textarea', array(
            'label' => 'Description',
            'required' => false,
            'attr' => array(
                'placeholder' => 'Description',
                'class' => 'textarea-non-resizeable width-280 description',
                'maxlength' => '100'
            )
        ));

        $builder->add('category', 'entity', array(
            'class' => 'OpitOpitHrmLeaveBundle:LeaveCategory',
            'property' => 'name',
            'attr' => array('class' => 'leave-category'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
            }
        ));
    }

    /**
     * Sets the default form options
     *
     * @param object $resolver An OptionsResolver interface object
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\OpitHrm\LeaveBundle\Entity\Leave'
        ));
    }
    /**
     * Get the name
     *
     * @return string name
     */
    public function getName()
    {
        return 'leave';
    }
}
