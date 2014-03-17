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
use Doctrine\ORM\EntityRepository;

/**
 * Description of DestinationType
 *
 * @author OPIT\Notes
 */
class DestinationType extends AbstractType
{
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('transportation_type', 'entity', array(
            'class'  => 'OpitNotesTravelBundle:TransportationType',
            'property' => 'name',
            'required' => 'true',
            'empty_value' => 'Choose...',
            'label'=>'Transportation type',
            'query_builder' => function (EntityRepository $repository) {
                 return $repository->createQueryBuilder('u')->orderBy('u.name', 'DESC');
            }
         ));
        
        $builder->add('name', 'text', array(
            'label'=>'Destination name',
            'attr' => array(
                'placeholder' => 'Destination name'
            )
        ));
        
        $builder->add('cost', 'integer', array(
            'attr' => array(
                'class' => 'cost display-inline-block-important width-80',
                'placeholder' => 'Cost',
                'min' => '1'
            )
        ));
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
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TRDestination',
        ));
    }

    public function getName()
    {
        return '';
    }
}
