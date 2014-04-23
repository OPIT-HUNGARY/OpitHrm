<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\DependencyInjection;

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
 * @subpackage CurrencyRateBundle
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opit_notes_currency_rate');

        $rootNode
            ->children()
                ->scalarNode('default_currency')->cannotBeEmpty()->defaultValue('EUR')->end()
                ->arrayNode('currency_format')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('decimals')->cannotBeEmpty()->defaultValue(2)->end()
                        ->scalarNode('dec_point')->cannotBeEmpty()->defaultValue(',')->end()
                        ->scalarNode('thousands_sep')->cannotBeEmpty()->defaultValue('.')->end()
                    ->end()
                ->end()
                ->arrayNode('mid_rate')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('day')->cannotBeEmpty()->defaultValue(15)->end()
                        ->scalarNode('modifier')->cannotBeEmpty()->defaultValue('-1 month')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
