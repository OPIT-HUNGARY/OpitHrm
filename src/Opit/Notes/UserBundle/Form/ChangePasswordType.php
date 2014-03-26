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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Change password form type
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes/UserBundle
 */
class ChangePasswordType extends AbstractType
{
    private $addSubmit;
    
    /**
     * 
     * @param boolean $addSubmit
     */
    public function __construct($addSubmit = false)
    {
        $this->addSubmit = $addSubmit;
    }
    
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', 'repeated', array(
        'type' => 'password',
        'first_options' => array('label' => 'New password', 'attr' => array('class' => 'margin-center margin-bottom-10')),
        'second_options' => array('label' => 'Confirm', 'attr' => array('class' => 'margin-center margin-bottom-10')),
        'invalid_message' => 'Passwords do not match.'
        ));
        
        if (true === $this->addSubmit) {
            $builder->add('change_password', 'submit', array('attr' => array('class' => 'button')));
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
            'validation_groups' => array('password')
        ));
    }
    /**
     * Get the name
     *
     * @return string name
     */
    public function getName()
    {
        return 'user_password';
    }
}
