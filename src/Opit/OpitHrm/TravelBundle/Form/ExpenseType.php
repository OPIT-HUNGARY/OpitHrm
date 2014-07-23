<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of ExpenseType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class ExpenseType extends AbstractType
{
    private $isNew;
    private $employee;
    
    public function __construct($employee, $roleFlag = false, $isNew = false)
    {
        $this->employee = $employee;
        $this->isNew = $isNew;
    }
    
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['em'];
        
        $builder->add('user_name', 'text',
            array(
                'label' => 'Employee name',
                'mapped' => false,
                'data' => $this->employee->getEmployeeName(),
                'attr' => array('placeholder' => 'Name', 'class' => 'te-claim'),
                'disabled' => true
            )
        );
        
        $builder->add('taxIdentification', 'text', array(
            'label' => 'Tax id',
            'data' => $this->employee->getTaxIdentification(),
            'attr' => array('placeholder' => 'Tax id', 'class' => 'te-claim')
        ));
        
        $builder->add('rechargeable', 'choice', array(
            'label' => 'Expense is rechargeable',
            'required' => true,
            'choices' => array('1'=>'No', '0'=>'Yes'),
            'attr' => array('class' => 'te-claim')
        ));
        
        $builder->add('payInEuro', 'choice', array(
            'label' => 'Pay in euro',
            'choices' => array('1'=>'No', '0'=>'Yes'),
            'attr' => array('placeholder' => 'Pay in euro', 'class' => 'te-claim')
        ));
        
        $builder->add('bankName', 'text', array(
            'label' => 'Bank name',
            'data' => $this->employee->getBankName(),
            'attr' => array('placeholder' => 'Bank name', 'class' => 'te-claim')
        ));
        
        $builder->add('bankAccountNumber', 'text', array(
            'label' => 'Bank account number',
            'data' => $this->employee->getBankAccountNumber(),
            'attr' => array('placeholder' => 'Bank account number', 'class' => 'te-claim')
        ));
        
        $builder->add('departureCountry', 'text', array(
            'label' => 'Departure country',
            'attr' => array('placeholder' => 'Departure country', 'class' => 'te-claim')
        ));
        $builder->add('departureDateTime', 'datetime', array(
            'date_widget' => 'single_text',
            'label' => 'Departure date time',
            'attr' => array('placeholder' => 'Departure date time', 'class' => 'te-claim')
        ));
        
        $builder->add('arrivalCountry', 'text', array(
            'label' => 'Target country',
            'attr' => array('placeholder' => 'Arrival country', 'class' => 'te-claim')
        ));
        $builder->add('arrivalDateTime', 'datetime', array(
            'date_widget' => 'single_text',
            'label' => 'Arrival date time',
            'attr' => array('placeholder' => 'Arrival date time', 'class' => 'te-claim')
        ));
        
        $builder->add('advancesReceived', 'collection', array(
            'type' => new TEAdvancesReceivedType($entityManager),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false
        ));
        
        $builder->add('companyPaidExpenses', 'collection', array(
            'type' => new CompanyPaidExpenseType(),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false
        ));
        
        $builder->add('userPaidExpenses', 'collection', array(
            'type' => new UserPaidExpenseType(),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false
        ));
        
        $builder->add('add_travel_expense', 'submit', array(
            'label'=>$this->isNew ? 'Edit travel expense' : 'Add travel expense',
            'attr' => array('class' => 'button')
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\OpitHrm\TravelBundle\Entity\TravelExpense'
        ))
        ->setRequired(array(
            'em',
        ))
        ->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return 'travelExpense';
    }
}
