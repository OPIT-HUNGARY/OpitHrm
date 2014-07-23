<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Description of UserPaidExpenseType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
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
            'attr' => array('placeholder' => 'Date', 'class' => 'display-inline-block-important')
        ));
        $builder->add('expense_type', 'entity', array(
            'class'  => 'OpitNotesTravelBundle:TEExpenseType',
            'property' => 'name',
            'required' => 'true',
            'empty_value' => 'Choose...',
            'label'=>'Expense type',
            'attr' => array('class' => 'te-expense-type'),
            'query_builder' => function (EntityRepository $er) {
                 return $er->createQueryBuilder('u')->orderBy('u.name', 'ASC');
            }
        ));
        $builder->add('amount', 'number', array(
            'label' => 'Amount',
            'attr' => array(
                'class' => 'amount amount-listen display-inline-block-important width-80',
                'placeholder' => 'Amount'
                )
        ));
        $builder->add('currency', 'entity', array('attr' => array(
                'class' => 'currency currency-listen display-inline-block margin-left-5'
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
