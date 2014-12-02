<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Opit\OpitHrm\TravelBundle\Form\DataTransformer\UserIdToObjectTransformer;

/**
 * Description of JobPositionType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class JobPositionType extends AbstractType
{
    protected $isNewJobPosition;

    public function __construct($isNewJobPosition)
    {
        $this->isNewJobPosition = $isNewJobPosition;
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
        $userTransformer = new UserIdToObjectTransformer($entityManager);

        $builder->add('jobTitle', 'text', array(
            'label' => 'Job title',
            'required' => true,
            'attr' => array(
                'placeholder' => 'Job title',
                'class' => 'width-200'
            )
        ));

        $builder->add('numberOfPositions', 'integer', array(
            'label' => 'No. of positions',
            'required' => true,
            'attr' => array(
                'class' => 'width-50'
            )
        ));

        $builder->add('description', 'textarea', array(
            'label' => 'Description',
            'required' => true,
            'attr' => array(
                'placeholder' => 'Description',
                'class' => 'width-300 height-150'
            )
        ));

        $builder->add(
            $builder->create('hiring_manager', 'hidden')->addModelTransformer($userTransformer)
        );
        $builder->add('hiring_manager_ac', 'text', array(
            'label' => 'Hiring manager',
            'data' => ($user = $options['data']->getHiringManager()) ? $user->getEmployee()->getEmployeeNameFormatted() : null,
            'mapped' => false,
            'required' => true,
            'attr' => array('placeholder' => 'Hiring manager', 'class' => 'width-300')
        ));

        $builder->add('location', 'entity', array(
            'class' => 'OpitOpitHrmHiringBundle:Location',
            'property' => 'name',
            'placeholder' => 'Choose an option',
            'attr' => array('class' => 'display-inline-block'),
            'label_attr' => array('class' => 'display-inline-block'),
            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                $qb = $er->createQueryBuilder('l');
                return $qb->where(
                        $qb->expr()->isNull('l.deletedAt')
                    )
                    ->orderBy('l.name', 'ASC');
            }
        ));

        $builder->add('isActive', 'choice', array(
            'choices'   => array('1' => 'Yes', '0' => 'No'),
            'required'  => true,
            'label' => 'Active'
        ));

        $builder->add('create_job_position', 'submit', array(
            'label' => $this->isNewJobPosition ? 'Add job position' : 'Edit job position',
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
            'data_class' => 'Opit\OpitHrm\HiringBundle\Entity\JobPosition'
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
        return 'job_position';
    }
}
