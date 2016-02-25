<?php

namespace Gamma\FixturesBoostBundle\DependencyInjection;

use Gamma\FixturesBoostBundle\Service\FixturesBoostService;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gamma_fixtures_boost');

        $rootNode
            ->children()
                ->scalarNode('working_dir')->defaultValue('%kernel.root_dir%/..')->end()
                ->enumNode('clear')
                    ->values(array(FixturesBoostService::CLEAR_MODE_SCHEMA, FixturesBoostService::CLEAR_MODE_DATABASE))
                    ->defaultValue(FixturesBoostService::CLEAR_MODE_SCHEMA)
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
