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

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Opit\Notes\UserBundle\Form\DataTransformer\SimpleIntegerToStringTransformer;
use Opit\Notes\UserBundle\Form\TeamType;

/**
 * Description of ContactType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class UserShowType extends AbstractType
{

    /**
     * Entity Manager
     * @var object of EntityManager
     */
    protected $em;

    /**
     * Container
     * @var object of Container
     */
    protected $container;

    /**
     * Constructor for this class.
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * Builds a form with given fields.
     *
     * @param object $builder A Formbuilder interface object
     * @param array  $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataArr = $builder->getData();
        $config = $this->container->getParameter('opit_notes_user');
        $userId = null;
        $isAdmin = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        // If we modify an existed user.
        if (null !== $dataArr) {
            $userId = $dataArr->getId();
        }

        // If the current user has admin role then the field will be changeable
        if (true === $isAdmin) {
            $builder->add('username', 'text', array('attr' => array(
                'placeholder' => 'Username'
            )));
        }

        $builder->add('email', 'text', array('attr' => array(
            'placeholder' => 'Email'
        )));
        $builder->add('employeeName', 'text', array('attr' => array(
            'placeholder' => 'Employee Name'
        )));

        $tax = $builder->create('taxIdentification', 'integer', array(
            'attr' => array(
                'placeholder' => 'Tax number'
            ),
            'invalid_message' => 'You entered an invalid value - it should be an integer'
        ));
        // If the php's version is less than the required min php version then load the data transformet class.
        if ($config['min_php_version'] > phpversion()) {
            $tax->resetViewTransformers();
            $tax->addViewTransformer(new SimpleIntegerToStringTransformer());
        }
        $builder->add($tax);

        $builder->add('bankAccountNumber', 'text', array('attr' => array(
            'placeholder' => 'Bank account number'
        )));
        $builder->add('bankName', 'text', array('attr' => array(
            'placeholder' => 'Bank Name'
        )));

        $builder->add('userId', 'hidden', array('data' => $userId, 'mapped' => false));

        if (true === $isAdmin) {
            $builder->add('groups', 'entity', array(
                'class' => 'OpitNotesUserBundle:Groups',
                'property' => 'name',
                'multiple' => true,
                'expanded' => true
            ));

            $builder->add('jobTitle', 'entity', array(
                'class' => 'OpitNotesUserBundle:JobTitle',
                'property' => 'title',
                'multiple' => false,
                'data' => $dataArr->getJobTitle()
            ));

            $builder->add('isActive', 'choice', array(
                'choices' => $this->container->getParameter('notes_user_status')
            ));

            // Display ldap feature related form inputs
            if (isset($config['ldap']['enabled']) && true === $config['ldap']['enabled']) {
                $builder->add('ldapEnabled', 'choice', array(
                    'choices'   => array('No', 'Yes'),
                    'multiple' => false,
                    'expanded' => true,
                    'data' => $dataArr->isLdapEnabled() || 0
                ));
            }
            
            $builder->add('employee', new EmployeeType());
        }
    }
    /**
     * Sets the default form options
     *
     * @param object $resolver An OptionsResolver interface object
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\UserBundle\Entity\User',
            'validation_groups' => array('user')
        ));
    }
    /**
     * Get the name
     *
     * @return string name
     */
    public function getName()
    {
        return 'user';
    }
}
