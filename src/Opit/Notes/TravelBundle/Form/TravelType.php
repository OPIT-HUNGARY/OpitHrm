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
use Opit\Notes\TravelBundle\Form\DataTransformer\UserIdToObjectTransformer;

/**
 * Description of TravelType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TravelType extends AbstractType
{
    private $isGranted;
    private $isNew;
    
    public function __construct($roleFlag = false, $isNew = false)
    {
        $this->isGranted = $roleFlag;
        $this->isNew = $isNew;
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
        $transformer = new UserIdToObjectTransformer($entityManager);
        
        $userAcOptions = array();
        
        // Disable employee form if access is not granted
        // Currently access is only granted for ROLE_ADMIN.
        if ($options['data']->getUser() instanceof \Opit\Notes\UserBundle\Entity\User) {
            if (false === $this->isGranted) {
                $userAcOptions['disabled'] = true;
            }
        }
        
        $builder->add($builder->create('user', 'hidden')->addModelTransformer($transformer));
        $builder->add('user_ac', 'text', array_merge(array(
            'label' => 'Employee name',
            'data' => ($user = $options['data']->getUser()) ? $user->getEmployee()->getEmployeeNameFormatted() : null,
            'mapped' => false,
            'attr' => array('placeholder' => 'Employee name', 'class' => 'width-300')
        ), $userAcOptions));
        $builder->add('departure_date', 'date', array(
            'widget' => 'single_text',
            'label'=>'Departure date',
            'attr' => array('placeholder' => 'Departure date')
        ));
        $builder->add('arrival_date', 'date', array(
            'widget' => 'single_text',
            'label'=>'Arrival date',
            'attr' => array('placeholder' => 'Arrival date')
        ));
        $builder->add('customer_related', 'choice', array(
            'required' => false,
            'empty_value' => false,
            'data' => 'No',
            'label'=>'Customer related',
            'choices' => array('1'=>'No', '0'=>'Yes')
        ));
        $builder->add('customer_name', 'text', array(
            'label'=>'Customer name',
            'required' => false,
            'attr' => array('placeholder' => 'Customer name')
        ));
        $builder->add('trip_purpose', 'text', array(
            'label'=>'Trip purpose',
            'attr' => array('placeholder' => 'Trip purpose', 'class' => 'width-300')
        ));
        $builder->add('destinations', 'collection', array(
            'type' => new DestinationType(),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false
        ));
        $builder->add('accomodations', 'collection', array(
            'type' => new AccomodationType($entityManager),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false
        ));

        $builder->add(
            $builder->create('team_manager', 'hidden')->addModelTransformer($transformer)
        );
        $builder->add('team_manager_ac', 'text', array(
            'label' => 'Team manager',
            'data' => ($user = $options['data']->getTeamManager()) ? $user->getEmployee()->getEmployeeNameFormatted() : null,
            'mapped' => false,
            'required' => false,
            'attr' => array('placeholder' => 'Team manager', 'class' => 'width-300')
        ));
        $builder->add(
            $builder->create('general_manager', 'hidden')->addModelTransformer($transformer)
        );
        $builder->add('general_manager_ac', 'text', array(
            'label' => 'General manager',
            'data' => ($user = $options['data']->getGeneralManager()) ? $user->getEmployee()->getEmployeeNameFormatted() : null,
            'mapped' => false,
            'attr' => array('placeholder' => 'General manager', 'class' => 'width-300')
        ));
        
        $builder->add('add_travel_request', 'submit', array(
            'label'=>$this->isNew ? 'Edit travel request' : 'Add travel request',
            'attr' => array('class' => 'button')
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\Notes\TravelBundle\Entity\TravelRequest'
        ))
        ->setRequired(array(
            'em',
        ))
        ->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }

    public function getName()
    {
        return 'travelRequest';
    }
}
