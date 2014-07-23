<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Twig OpitExtension class
 *
 * @author OPIT Consulting Kft. - OPIT-HRM Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class OpitExtension extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array(
            'ldap_enabled' => $this->container->hasParameter('ldap_enabled'),
            'security_roles' => array_keys($this->container->getParameter('security.role_hierarchy.roles'))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opit_extension';
    }
}
