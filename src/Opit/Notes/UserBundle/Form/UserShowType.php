<?php

/*
 * This file is part of the Opit/Notes/User bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Opit\Notes\UserBundle\Form\DataTransformer\SimpleIntegerToStringTransformer;

/**
 * Description of ContactType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes/UserBundle
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
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataArr = $builder->getData();
        $config = $this->container->getParameter('opit_notes_user');
        $userId = null;

        // If we modify an existed user.
        if (null !== $dataArr) {
            $userId = $dataArr->getId();
        }

        $builder->add('username', 'text', array('attr' => array(
            'placeholder' => 'Username'
        )));
        $builder->add('email', 'text', array('attr' => array(
            'placeholder' => 'Email'
        )));
        $builder->add('employeeName', 'text', array('attr' => array(
            'placeholder' => 'Employee Name'
        )));
        // If the current user has admin role then the field will be changeable
        if (true === $this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            $builder->add('isActive', 'choice', array(
                'choices' => $this->container->getParameter('notes_user_status')
            ));
        }
        $builder->add('jobTitle', 'entity', array(
            'class' => 'OpitNotesUserBundle:JobTitle',
            'property' => 'title',
            'multiple' => false,
            'data' => $dataArr->getJobTitle()
        ));
        // If the current user has admin role then the field will be changeable
        if (true === $this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            $builder->add('groups', 'entity', array(
                'class' => 'OpitNotesUserBundle:Groups',
                'property' => 'name',
                'multiple' => true,
                'expanded' => true
            ));
        }
        $builder->add('bankAccountNumber', 'text', array('attr' => array(
            'placeholder' => 'Bank account number'
        )));
        $builder->add('bankName', 'text', array('attr' => array(
            'placeholder' => 'Bank Name'
        )));
        $tax = $builder->create('taxIdentification', 'integer', array('attr' => array(
            'placeholder' => 'Tax number'
        )));
        
        // If the php's version is less than the required min php version then load the data transformet class.
        if ($config['min_php_version'] > phpversion()) {
            $tax->resetViewTransformers();
            $tax->addViewTransformer(new SimpleIntegerToStringTransformer());
        }
        $builder->add($tax);
        
        if (null === $userId) {
            $builder->add('password', 'repeated', array(
                'first_name' => 'password',
                'second_name' => 'confirm',
                'type' => 'password',
                'invalid_message' => 'Passwords do not match'
            ));
        }
        $builder->add('userId', 'hidden', array('data' => $userId, 'mapped' => false));
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
