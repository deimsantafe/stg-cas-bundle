<?php

namespace STG\DEIM\Security\Bundle\CasBundle\DependencyInjection;

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
        $treeBuilder->root('stg_cas')
            ->children()
                ->scalarNode('server')->defaultFalse()->end()
                ->variableNode('url')->end()
                ->scalarNode('cert')->defaultFalse()->end()
                ->scalarNode('username_attribute')->defaultValue('user')->end()
                ->scalarNode('proxy')->defaultFalse()->end()
                ->scalarNode('callback')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
