<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Opit\Notes\LeaveBundle\Form\LeaveType;
use Opit\Notes\LeaveBundle\Form\DataTransformer\EmployeeIdToObjectTransformer;
use Opit\Notes\TravelBundle\Form\DataTransformer\UserIdToObjectTransformer;

/**
 * Description of LeaveRequestType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class LeaveRequestType extends AbstractType
{
    private $isNewLeaveRequest;

    public function __construct($isNewLeaveRequest)
    {
        $this->isNewLeaveRequest = $isNewLeaveRequest;
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
        $employeeTransformer = new EmployeeIdToObjectTransformer($entityManager);
        $userTransformer = new UserIdToObjectTransformer($entityManager);
        $builder->add($builder->create('employee', 'hidden')->addModelTransformer($employeeTransformer));
        
        $builder->add('user_ac', 'text', array(
            'label' => '',
            'data' => ($employee = $options['data']->getEmployee()) ? $employee->getEmployeeName() . ' <' . $employee->getUser()->getEmail() . '>' : null,
            'mapped' => false,
            'disabled' => true,
            'attr' => array('placeholder' => 'Employee name', 'class' => 'width-300')
        ));

        $builder->add('leaves', 'collection', array(
            'type'         => new LeaveType(),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false
        ));

        $builder->add(
            $builder->create('team_manager', 'hidden')->addModelTransformer($userTransformer)
        );
        $builder->add('team_manager_ac', 'text', array(
            'label' => 'Team manager',
            'data' => ($user = $options['data']->getTeamManager()) ? $user->getEmployee()->getEmployeeNameFormatted() : null,
            'mapped' => false,
            'required' => false,
            'attr' => array('placeholder' => 'Team manager', 'class' => 'width-300')
        ));
        $builder->add(
            $builder->create('general_manager', 'hidden')->addModelTransformer($userTransformer)
        );
        $builder->add('general_manager_ac', 'text', array(
            'label' => 'General manager',
            'data' => ($user = $options['data']->getGeneralManager()) ? $user->getEmployee()->getEmployeeNameFormatted() : null,
            'mapped' => false,
            'attr' => array('placeholder' => 'General manager', 'class' => 'width-300')
        ));

        $builder->add('create_leave_request', 'submit', array(
            'label' => $this->isNewLeaveRequest ? 'Add leave request' : 'Edit leave request',
            'attr' => array('class' => 'button')
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
            'data_class' => 'Opit\Notes\LeaveBundle\Entity\LeaveRequest',
            'validation_groups' => array('user')
        ))
        ->setRequired(array(
            'em',
        ))
        ->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
    /**
     * Get the name
     *
     * @return string name
     */
    public function getName()
    {
        return 'leave_request';
    }
}
