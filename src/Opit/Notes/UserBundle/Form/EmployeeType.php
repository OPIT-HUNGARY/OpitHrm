<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Opit\Notes\UserBundle\Form\DataTransformer\SimpleIntegerToStringTransformer;
use Doctrine\ORM\EntityRepository;

/**
 * Description of EmployeeType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class EmployeeType extends AbstractType
{
    /**
     * Container
     * @var object of Container
     */
    protected $container;
    protected $employee;

    /**
     * Constructor for this class.
     *
     * @param object $container
     */
    public function __construct(ContainerInterface $container, $employee)
    {
        $this->container = $container;
        $this->employee = $employee;
    }

    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->container->getParameter('opit_notes_user');
        $leaveConfig = $this->container->getParameter('opit_notes_leave');

        $builder->add('employeeName', 'text', array(
            'attr' => array(
                'placeholder' => 'Employee Name'
            )
        ));

        $tax = $builder->create('taxIdentification', 'integer', array(
            'attr' => array(
                'placeholder' => 'Tax number'
            ),
            'invalid_message' => 'You entered an invalid value - it should be an integer'
        ));

        // If the php's version is less than the required min php version then load the data transformer class.
        if ($config['min_php_version'] > phpversion()) {
            $tax->resetViewTransformers();
            $tax->addViewTransformer(new SimpleIntegerToStringTransformer());
        }
        $builder->add($tax);

        $builder->add('bankAccountNumber', 'text', array(
            'attr' => array(
                'placeholder' => 'Bank account number'
            )
        ));

        $builder->add('bankName', 'text', array(
            'attr' => array(
                'placeholder' => 'Bank Name'
            )
        ));

        $builder->add('teams', 'entity', array(
            'label' => 'Teams',
            'class' => 'OpitNotesUserBundle:Team',
            'property' => 'teamName',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'query_builder' => function (EntityRepository $er) {
                $dq = $er->createQueryBuilder('t')
                    ->orderBy('t.teamName', 'ASC');

                return $dq;
            },
            'label_attr' => array('id' => 'idTeam')
        ));

        $builder->add('numberOfChildren', 'integer', array(
            'label' => 'No. Of Children (< 30)',
            'invalid_message' => 'No. of children can only contain integer values.',
            'attr' => array(
                'min' => 0,
                'max' => 30,
                'placeholder' => 'Number of children'
            ),
            'label_attr' => array('id' => 'idNoc')
        ));

        $builder->add('joiningDate', 'date', array(
            'widget' => 'single_text',
            'attr' => array(
                'placeholder' => 'Joining date'
            ),
            'label_attr' => array('id' => 'idJoiningDate')
        ));

        $builder->add('dateOfBirth', 'date', array(
            'widget' => 'single_text',
            'attr' => array(
                'placeholder' => 'Date of birth'
            ),
            'label_attr' => array('id' => 'idDob')
        ));

        $builder->add('workingHours', 'integer', array(
            'invalid_message' => 'Working hours can only contain integer values.',
            'attr' => array(
                'min' => 0,
                'max' => 24,
                'placeholder' => 'Working hours'
            )
        ));

        // Add admin only forms
        if ($this->container->get('security.context')->isGranted('ROLE_SYSTEM_ADMIN')) {
            $builder->add('jobTitle', 'entity', array(
                'class' => 'OpitNotesUserBundle:JobTitle',
                'property' => 'title',
                'multiple' => false,
                'label_attr' => array('id' => 'idJobTitle'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('j')->orderBy('j.title', 'ASC');
                }
            ));

            // If the leave settings configuration is disabled then this form option will be viewed
            if (isset($leaveConfig['leave_entitlement_plan']['enabled']) && false === $leaveConfig['leave_entitlement_plan']['enabled']) {
                $builder->add('entitledLeaves', 'integer', array(
                    'label' => 'Yearly Leave Entitlement',
                    'data' => ($entLeaves = $this->employee->getEntitledLeaves()) ? $entLeaves : $leaveConfig['leave_entitlement_plan']['default_days'],
                    'attr' => array(
                        'placeholder' => 'Yearly leave entitlement',
                    )
                ));
            }
        }
    }

    /**
     * Sets the default form options
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\UserBundle\Entity\Employee',
            'validation_groups' => array('employee')
        ));
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'employee';
    }
}
