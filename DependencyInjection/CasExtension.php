<?php

namespace STG\DEIM\Security\Bundle\CasBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class CasExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('stg_cas.url', $config['url']);
        $container->setParameter('stg_cas.server', $config['server'] ?? $config['url']);
        $container->setParameter('stg_cas.cert', $config['cert']);
        $container->setParameter('stg_cas.username_attribute', $config['username_attribute']);
        $container->setParameter('stg_cas.proxy', $config['proxy']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }
}
