<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Description of AccomodationType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class AccomodationType extends AbstractType
{
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hotel_name', 'text', array(
            'label'=>'Hotel name',
            'attr' => array(
                'placeholder' => 'Hotel name'
            )
        ));
        $builder->add('city', 'text', array('attr' => array(
            'placeholder' => 'City'
        )));
        $builder->add('number_of_nights', 'integer', array(
            'label'=>'Number of nights',
            'attr' => array(
                'placeholder' => 'Number of nights',
                'min' => '1',
                'class' => 'number-of-nights'
            )
        ));
        $builder->add('cost', 'number', array('attr' => array(
                'class' => 'cost display-inline-block-important width-80',
                'placeholder' => 'Cost'
            ),
            'pattern' => "^[0-9]+([\,\.][0-9]+)?$"
        ));
        $builder->add('currency', 'entity', array('attr' => array(
                'class' => 'currency display-inline-block margin-left-5'
            ),
            'label' => false,
            'class' => 'OpitNotesCurrencyRateBundle:Currency',
            'property' => 'code',
            'multiple' => false,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.code', 'ASC');
            }
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TRAccomodation',
        ));
    }

    public function getName()
    {
        return '';
    }
}
