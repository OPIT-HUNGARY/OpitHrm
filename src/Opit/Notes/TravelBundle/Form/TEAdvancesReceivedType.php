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
use Doctrine\ORM\EntityManager;

/**
 * Description of ExpenseType
 *
 * @author OPIT\kaufmann
 */
class TEAdvancesReceivedType extends AbstractType
{
    private $entityManager;
        
    /**
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'advancesReceived',
            'integer',
            array(
                'label' => 'Advances received',
                'attr' => array(
                    'placeholder' => 'Advances received',
                    'class' => 'te-advances-received'
                )
            )
        );
        
        $builder->add(
            'currency',
            'entity',
            array(
                'class' => 'OpitNotesCurrencyRateBundle:Currency',
                'property' => 'code',
                'multiple' => false,
                'attr' => array(
                    'class' => 'te-advances-received-currency'
                )
            )
        );
    }
    
    /**
     * 
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TEAdvancesReceived'
        ));
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return 'teAdvancesReceived';
    }
}
