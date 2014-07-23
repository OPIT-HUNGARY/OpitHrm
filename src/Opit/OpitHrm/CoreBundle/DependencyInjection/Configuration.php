<?php

namespace Opit\OpitHrm\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opit_opit_hrm_core');

        $rootNode
            ->children()
                ->arrayNode('mailing')
                    ->isRequired()
                    ->children()
                        ->scalarNode('mail_sender')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('currency')
                    ->isRequired()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_currency')->cannotBeEmpty()->defaultValue('%default_currency%')->end()
                        ->arrayNode('currency_format')
                            ->children()
                                ->integerNode('decimals')->cannotBeEmpty()->defaultValue(2)->end()
                                ->scalarNode('dec_point')->cannotBeEmpty()->defaultValue('.')->end()
                                ->scalarNode('thousands_sep')->cannotBeEmpty()->defaultValue(',')->end()
                            ->end()
                        ->end()
                        ->arrayNode('mid_rate')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('day')->cannotBeEmpty()->defaultValue(1)->end()
                                ->scalarNode('modifier')->cannotBeEmpty()->defaultValue('+0 month')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('pager')
                    ->isRequired()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('max_results')->cannotBeEmpty()->defaultValue('%max_results%')->end()
                        ->integerNode('max_pages')->cannotBeEmpty()->defaultValue('%max_pages%')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
