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
 * Description of ExpenseType
 *
 * @author OPIT\kaufmann
 */
class ExpenseType extends AbstractType
{
    private $isGranted;
    private $isNew;
    
    public function __construct($roleFlag = false, $isNew = false)
    {
        $this->isGranted = $roleFlag;
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
        $userAttributes = array('placeholder' => 'Name', 'class' => 'te-claim');
        $entityManager = $options['em'];
        $user = $options['data']->getUser();
        $taxId = null;
        $bankName = null;
        $bankAccountNumber = null;
        $employeeName = null;

        if (null !== $user) {
            $taxId = ($tid = $options['data']->getTaxIdentification()) ? $tid : $user->getTaxIdentification();
            $bankName = ($bName = $options['data']->getBankName()) ? $bName : $user->getBankname();
            $bankAccountNumber =
                ($bAccNumber = $options['data']->getBankAccountNumber()) ? $bAccNumber : $user->getBankAccountNumber();
            $employeeName =
                ($eName = $options['data']->getUser()->getEmployeeName()) ? $eName : $user->getEmployeeName();
        }
        
        if ($options['data']->getUser() instanceof \Opit\Notes\UserBundle\Entity\User) {
            if (false === $this->isGranted) {
                $userAttributes['disabled'] = 'disabled';
            }
        }
        
        $builder->add('user_name', 'text', array(
            'label' => 'Employee name',
            'mapped' => false,
            'data' => $employeeName,
            'attr' => $userAttributes
        ));
        
        $builder->add('taxIdentification', 'text', array(
            'label' => 'Tax id',
            'data' => $taxId,
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
            'data' => $bankName,
            'attr' => array('placeholder' => 'Bank name', 'class' => 'te-claim')
        ));
        
        $builder->add('bankAccountNumber', 'text', array(
            'label' => 'Bank account number',
            'data' => $bankAccountNumber,
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
        
        $builder->add('teAdvancesReceived', 'collection', array(
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
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TravelExpense'
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
