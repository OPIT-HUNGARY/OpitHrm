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
use Doctrine\ORM\EntityManager;

/**
 * Description of ExpenseType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TEAdvancesReceivedType extends AbstractType
{
    private $entityManager;
        
    /**
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'advancesReceived',
            'number',
            array(
                'label' => 'Advances received',
                'attr' => array(
                    'placeholder' => 'Advances received',
                    'class' => 'te-advances-received'
                )
            )
        );
        
        $builder->add(
            'currency',
            'entity',
            array(
                'class' => 'OpitNotesCurrencyRateBundle:Currency',
                'property' => 'code',
                'multiple' => false,
                'attr' => array(
                    'class' => 'te-advances-received-currency'
                )
            )
        );
    }
    
    /**
     * 
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TEAdvancesReceived'
        ));
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return 'teAdvancesReceived';
    }
}
