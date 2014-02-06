<?php

namespace Opit\Notes\TravelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PerDiemType extends AbstractType
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $inArray = array_key_exists('data', $options);
        $builder->add('hours', 'integer', array(
            'label' => 'Hours',
            'attr' => array('placeholder' => 'Hours', 'class' => 'te-claim hours width-80-fix', 'min'=>1, 'max'=>24)
        ));
        $builder->add('amount', 'integer', array(
            'label' => 'Amount',
            'attr' => array('placeholder' => 'Amount', 'class' => 'te-claim amount width-80-fix', 'min'=>1)
        ));
        $builder->add('currency', 'entity', array('attr' => array(
                'class' => 'te-claim currency width-80-fix display-block'
            ),
            'label' => 'Currency',
            'class' => 'OpitNotesCurrencyRateBundle:Currency',
            'disabled' => true,
            'property' => 'code',
            'multiple' => false
        ));
        $builder->add('id', 'hidden', array(
            'mapped'=>false,
            'data' => $inArray?(($id = $options['data']->getId()) ? $id : ''):''
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TEPerDiem'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }
}
