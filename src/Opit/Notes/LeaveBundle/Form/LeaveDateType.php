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
use Doctrine\ORM\EntityRepository;

/**
 * Description of LeaveDateType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class LeaveDateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataArr = $builder->getData();
        
        $builder->add('holidayDate', 'date', array(
            'label' => 'Leave Date',
            'widget' => 'single_text',
        ));

        $builder->add('holidayType', 'entity', array(
            'label' => 'Leave Type',
            'class' => 'OpitNotesLeaveBundle:LeaveType',
            'property' => 'name',
            'multiple' => false,
            'data' => $dataArr->getHolidayType(),
            'label_attr' => array('id' => 'idLeaveType'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('lt')->orderBy('lt.name', 'ASC');
            }
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\LeaveBundle\Entity\LeaveDate'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'leaveDate';
    }
}
