<?php

namespace Opit\OpitHrm\LeaveBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('opit_opit_hrm_leave');

        $rootNode
            ->children()
                ->arrayNode('leave_entitlement_plan')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_days')
                            ->defaultValue('%leave_default_days%')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('time_sheet')
                    ->children()
                        ->scalarNode('arrival_time')
                            ->defaultValue('%arrival_time%')
                        ->end()
                    ->end()
                    ->children()
                        ->scalarNode('lunch_time_in_minutes')
                            ->defaultValue('%lunch_time_in_minutes%')
                        ->end()
                    ->end()
                    ->children()
                        ->scalarNode('user_grouping_number')
                            ->defaultValue('3')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('calculation_params')
                    ->children()
                        ->scalarNode('calendar_days')
                            ->cannotBeEmpty()
                            ->defaultValue('365')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
