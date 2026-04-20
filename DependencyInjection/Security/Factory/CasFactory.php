<?php

namespace STG\DEIM\Security\Bundle\CasBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CasFactory implements AuthenticatorFactoryInterface
{
    public const PRIORITY = -10;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function getKey(): string
    {
        return 'cas';
    }

    public function addConfiguration(NodeDefinition $node): void
    {
        $node->children()
            ->scalarNode('check_path')->defaultValue('/cas/check')->end()
            ->scalarNode('failure_path')->defaultValue('/')->end()
        ->end();
    }

    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        $authenticatorId = 'cas.authenticator.' . $firewallName;

        $container->setDefinition($authenticatorId, new ChildDefinition('cas.authenticator'))
            ->replaceArgument(3, new Reference($userProviderId))
            ->replaceArgument(4, $config['check_path'])
            ->replaceArgument(5, $config['failure_path']);

        return $authenticatorId;
    }
}
