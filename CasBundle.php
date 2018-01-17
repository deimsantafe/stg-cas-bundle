<?php

namespace STG\DEIM\Security\Bundle\CasBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use STG\DEIM\Security\Bundle\CasBundle\DependencyInjection\Security\Factory\CasFactory;

class CasBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new CasFactory());
    }
}
