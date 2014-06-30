<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of ExternalApplicantType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
class ExternalApplicantType extends AbstractType
{
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'Name',
            'required' => true,
            'attr' => array(
                'placeholder' => 'Name',
                'class' => 'width-200'
            )
        ));

        $builder->add('email', 'text', array(
            'label' => 'Email',
            'required' => true,
            'attr' => array(
                'placeholder' => 'Email',
                'class' => 'width-200'
            )
        ));

        $builder->add('keywords', 'text', array(
            'label' => 'Keywords',
            'required' => true,
            'attr' => array(
                'placeholder' => 'e.g. keyword, keyword, keyword',
                'class' => 'width-200'
            )
        ));

        $builder->add('phoneNumber', 'text', array(
            'label' => 'Phone number',
            'required' => true,
            'attr' => array(
                'placeholder' => 'Phone number',
                'class' => 'width-200'
            )
        ));

        $builder->add('cvFile', 'file',array(
                'required' => true,
                'label' => 'Upload CV (.doc, .docx, .pdf)'
            )
        );

        $builder->add('create_applicant', 'submit', array(
            'label' => 'Apply',
            'attr' => array('class' => 'button2 float-right margin-right-20')
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
                'data_class' => 'Opit\Notes\HiringBundle\Entity\Applicant'
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
        return 'external_applicant';
    }

}
