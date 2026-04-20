<?php

namespace STG\DEIM\Security\Bundle\CasBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cas');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('url')
                    ->isRequired()
                    ->info('URL base del servidor CAS (e.g. https://dsso.santafe.gob.ar/service-auth)')
                ->end()
                ->scalarNode('server')
                    ->defaultNull()
                    ->info('URL del servidor CAS para validación (por defecto igual a url)')
                ->end()
                ->scalarNode('cert')
                    ->defaultNull()
                    ->info('Ruta al certificado SSL (opcional)')
                ->end()
                ->scalarNode('username_attribute')
                    ->defaultValue('cuil')
                    ->info('Atributo del ticket CAS utilizado como identificador de usuario')
                ->end()
                ->booleanNode('proxy')
                    ->defaultFalse()
                    ->info('Habilitar modo proxy CAS')
                ->end()
            ->end();

        return $treeBuilder;
    }
}