<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Description of ContactType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class UserShowType extends AbstractType
{

    /**
     * Entity Manager
     * @var object of EntityManager
     */
    protected $em;

    /**
     * Container
     * @var object of Container
     */
    protected $container;

    /**
     * Constructor for this class.
     *
     * @param object $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Builds a form with given fields.
     *
     * @param object $builder A Formbuilder interface object
     * @param array  $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataArr = $builder->getData();
        $config = $this->container->getParameter('opit_opit_hrm_user');

        $builder->add('username', 'text', array('attr' => array(
                'placeholder' => 'Username'
        )));

        $builder->add('email', 'text', array('attr' => array(
                'placeholder' => 'Email'
        )));

        $builder->add('groups', 'entity', array(
                'class' => 'OpitOpitHrmUserBundle:Groups',
            'query_builder' => function (EntityRepository $er) {
                $dq = $er->createQueryBuilder('g');

                if (!$this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                    $dq->where('g.role IN (:allowedRoles)');
                    $dq->setParameter(':allowedRoles', $this->container->getParameter('security.role_hierarchy.roles')['ROLE_SYSTEM_ADMIN']);
                }

                return $dq->orderBy('g.name', 'ASC');
            },
            'property' => 'name',
            'multiple' => true,
            'expanded' => true,
            'label_attr' => array('id' => 'idGroups')
        ));

        $builder->add('isActive', 'choice', array(
                'choices' => $this->container->getParameter('opithrm_user_status')
        ));

        // Display ldap feature related form inputs
        if (isset($config['ldap']['enabled']) && true === $config['ldap']['enabled']) {
            $builder->add('ldapEnabled', 'choice', array(
                'choices'   => array('No', 'Yes'),
                'multiple' => false,
                'expanded' => true,
                'data' => $dataArr->isLdapEnabled() || 0
            ));
        }

        $builder->add('employee', new EmployeeType($this->container, $dataArr->getEmployee()));
    }

    /**
     * Sets the default form options
     *
     * @param object $resolver An OptionsResolver interface object
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Opit\OpitHrm\UserBundle\Entity\User',
            'validation_groups' => array('user', 'employee'),
        ));
    }
    /**
     * Get the name
     *
     * @return string name
     */
    public function getName()
    {
        return 'user';
    }
}
