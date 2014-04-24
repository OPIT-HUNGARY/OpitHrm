<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class OpitNotesUserExtension extends Extension
{
    /**
     * {@inheritDoc}
     * @throws \LogicException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('opit_notes_user', $config);
        // Check if ldap auth is enabled in bundle config
        if (isset($config['ldap']['enabled']) && true === $config['ldap']['enabled']) {
            // Check for required php extension
            if (!extension_loaded('ldap')) {
                throw new \LogicException('LDAP extension missing.');
            }

            $container->setParameter('ldap_enabled', $config['ldap']['enabled']);

            unset($config['ldap']['enabled']);
            // Used by the ldap authenticator service
            $container->setParameter('ldap', $config['ldap']);
            
            $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('ldap_services.xml');
        }
        
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
