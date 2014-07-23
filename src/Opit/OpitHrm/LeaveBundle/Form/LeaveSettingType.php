<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
namespace Opit\OpitHrm\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of LeaveSettingType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveSettingType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $inArray = array_key_exists('data', $options);
        
        $builder->add('number', 'integer', array('label' => 'Number', 'attr' => array(
            'min' => 0,
            'placeholder' => 'Number'
        )));
        $builder->add('numberOfLeaves', 'integer', array('label' => 'No. of leaves', 'attr' => array(
            'min' => 0,
            'placeholder' => 'Number of leaves'
        )));
        $builder->add('id', 'hidden', array(
            'mapped'=>false,
            'data' => $inArray?(($id = $options['data']->getId()) ? $id : ''):''
        ));
        
        $leaveGroup = $inArray?(($leaveGroup = $options['data']->getLeaveGroup()) ? $leaveGroup : ''):'';
        
        $builder->add('leaveGroup', 'hidden', array(
            'mapped'=>false,
            'data' => $leaveGroup?(($leaveGroupId = $leaveGroup->getId()) ? $leaveGroupId : ''):''
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\OpitHrm\LeaveBundle\Entity\LeaveSetting'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'opit_opithrm_LeaveBundle_holidaysetting';
    }
}
