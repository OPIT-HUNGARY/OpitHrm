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
     * @var object instance of EntityManager
     */
    protected $em;

    protected $status;

    /**
     * Constructor for this class.
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, $status)
    {
        $this->em = $em;
        $this->status = $status;
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
        $builder->add('isActive', 'choice', array('choices' => $this->status
        ));
        $builder->add('jobTitle', 'entity', array(
            'class' => 'OpitNotesUserBundle:JobTitle',
            'property' => 'title',
            'multiple' => false,
            'data' => $dataArr->getJobTitle()
        ));
        $builder->add('groups', 'entity', array(
            'class' => 'OpitNotesUserBundle:Groups',
            'property' => 'name',
            'multiple' => true,
            'expanded' => true
        ));
        $builder->add('bankAccountNumber', 'text', array('attr' => array(
            'placeholder' => 'Bank account number'
        )));
        $builder->add('bankName', 'text', array('attr' => array(
            'placeholder' => 'Bank Name'
        )));
        $builder->add('taxIdentification', 'integer', array('attr' => array(
            'placeholder' => 'Tax number'
        )));
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
