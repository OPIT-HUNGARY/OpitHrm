<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Description of TeamType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class TeamType extends AbstractType
{
     /**
     * Builds a form with given fields.
     *
     * @param object  $builder A Formbuilder interface object
     * @param array   $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('teamName', 'text', array(
            'label' => 'Team name',
            'attr' => array(
                'placeholder' => 'Team name'
            )
        ));

        $builder->add('employees', 'entity', array(
            'class' => 'OpitNotesUserBundle:Employee',
            'property' => 'employeeName',
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('e')
                    ->leftJoin('e.user', 'u')
                    ->innerJoin('u.groups', 'g')
                    ->where('g.role IN (:role)')
                    ->setParameter('role', 'ROLE_TEAM_MANAGER');
            }
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
            'data_class' => 'Opit\Notes\UserBundle\Entity\Team'
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
        return 'team';
    }
}
