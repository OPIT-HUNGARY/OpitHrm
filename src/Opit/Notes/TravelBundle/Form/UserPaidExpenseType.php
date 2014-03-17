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
 * Description of UserPaidExpenseType
 *
 * @author OPIT\kaufmann
 */
class UserPaidExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description', 'text', array(
            'label' => 'Description',
            'attr' => array('placeholder' => 'Description')
        ));
        $builder->add('date', 'date', array(
            'widget' => 'single_text',
            'label' => 'Date',
            'attr' => array('placeholder' => 'Date')
        ));
        $builder->add('expense_type', 'entity', array(
            'class'  => 'OpitNotesTravelBundle:TEExpenseType',
            'property' => 'name',
            'required' => 'true',
            'empty_value' => 'Choose...',
            'label'=>'Expense type',
            'attr' => array('class' => 'te-expense-type'),
            'query_builder' => function (EntityRepository $repository) {
                 return $repository->createQueryBuilder('u')->orderBy('u.name', 'DESC');
            }
        ));
        $builder->add('amount', 'integer', array(
            'label' => 'Amount',
            'attr' => array(
                'class' => 'amount display-inline-block-important width-80',
                'placeholder' => 'Amount',
                'min' => '1',
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
        $builder->add('destination', 'text', array(
            'label' => 'Destination',
            'attr' => array('placeholder' => 'Destination')
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TEUserPaidExpense'
        ));
    }

    public function getName()
    {
        return 'userPaidExpense';
    }
}
