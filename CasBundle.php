<?php

namespace STG\DEIM\Security\Bundle\CasBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use STG\DEIM\Security\Bundle\CasBundle\DependencyInjection\Security\Factory\CasFactory;

class CasBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        /** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addAuthenticatorFactory(new CasFactory());
    }
}
