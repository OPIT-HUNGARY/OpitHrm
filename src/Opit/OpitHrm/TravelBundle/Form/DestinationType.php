<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Description of DestinationType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class DestinationType extends AbstractType
{
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('transportation_type', 'entity', array(
            'class'  => 'OpitOpitHrmTravelBundle:TransportationType',
            'property' => 'name',
            'required' => 'true',
            'placeholder' => '-Select-',
            'label' => 'Transportation type',
            'query_builder' => function (EntityRepository $repository) {
                // Extend query to filter out softdeleted entities
                return $repository->createQueryBuilder('t')
                     ->where('t.deletedAt IS NULL')
                     ->orderBy('t.name', 'ASC');
            }
         ));

        $builder->add('name', 'text', array(
            'label' => 'Destination name',
            'attr' => array(
                'placeholder' => 'Destination name'
            )
        ));

        $builder->add('cost', 'number', array(
            'attr' => array(
                'class' => 'cost display-inline-block-important width-80',
                'placeholder' => 'Cost'
            ),
            'pattern' => "^[0-9]+([\,\.][0-9]+)?$"
        ));
        $builder->add('currency', 'entity', array('attr' => array(
                'class' => 'currency display-inline-block margin-left-5'
            ),
            'label' => false,
            'class' => 'OpitOpitHrmCurrencyRateBundle:Currency',
            'property' => 'code',
            'multiple' => false
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\OpitHrm\TravelBundle\Entity\TRDestination',
        ));
    }

    public function getName()
    {
        return '';
    }
}
