<?php


namespace Viking\Config;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CoreConfiguration implements ConfigurationInterface {

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('app')
            ->children()
                ->booleanNode('debug')
                    ->defaultFalse()
                ->end()
                ->scalarNode('startpage')
                    ->defaultValue('home')
                ->end()
                ->scalarNode('secret')
                    ->isRequired()
                ->end()
                ->scalarNode('root_dir')
                    ->isRequired()
                ->end()
                ->scalarNode('controller_dir')
                    ->defaultValue('/controllers')
                ->end()
            ->end();

        return $treeBuilder;
    }
}