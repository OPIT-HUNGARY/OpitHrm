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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opit_notes_user');

        $rootNode
            ->children()
                ->scalarNode('min_php_version')->cannotBeEmpty()->defaultValue('5.4.0')->end()
                ->arrayNode('ldap')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('%ldap_host%')
                        ->end()
                        ->scalarNode('bindRequiresDn')
                            ->cannotBeEmpty()
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('useSsl')
                            ->cannotBeEmpty()
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('accountDomainName')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('accountDomainNameShort')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('%ldap_domain_name_short%')
                        ->end()
                        ->scalarNode('accountCanonicalForm')
                            ->cannotBeEmpty()
                            ->defaultValue(3)
                            ->validate()
                            ->ifNotInArray(array(1, 2, 3, 4))
                                ->thenInvalid('Invalid account canonicalization form "%s"')
                            ->end()
                        ->end()
                        ->scalarNode('baseDn')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('%ldap_base_dn%')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
