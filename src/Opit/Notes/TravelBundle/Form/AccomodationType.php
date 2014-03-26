<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of AccomodationType
 *
 * @author OPIT\Notes
 */
class AccomodationType extends AbstractType
{
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hotel_name', 'text', array(
            'label'=>'Hotel name',
            'attr' => array(
                'placeholder' => 'Hotel name'
            )
        ));
        $builder->add('city', 'text', array('attr' => array(
            'placeholder' => 'City'
        )));
        $builder->add('number_of_nights', 'integer', array(
            'label'=>'Number of nights',
            'attr' => array(
                'placeholder' => 'Number of nights',
                'min' => '1',
                'class' => 'number-of-nights'
            )
        ));
        $builder->add('cost', 'number', array('attr' => array(
            'class' => 'cost display-inline-block-important width-80',
            'placeholder' => 'Cost'
        )));
        $builder->add('currency', 'entity', array('attr' => array(
                'class' => 'currency display-inline-block margin-left-5'
            ),
            'label' => false,
            'class' => 'OpitNotesCurrencyRateBundle:Currency',
            'property' => 'code',
            'multiple' => false
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TRAccomodation',
        ));
    }

    public function getName()
    {
        return '';
    }
}
