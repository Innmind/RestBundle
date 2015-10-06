<?php

namespace Innmind\RestBundle;

use Innmind\RestBundle\DependencyInjection\Compiler\RegisterClientDecodersPass;
use Innmind\RestBundle\DependencyInjection\Compiler\RegisterFormatPass;
use Innmind\RestBundle\DependencyInjection\Compiler\RegisterStoragePass;
use Innmind\RestBundle\DependencyInjection\Compiler\RegisterDefinitionCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InnmindRestBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new RegisterClientDecodersPass)
            ->addCompilerPass(new RegisterFormatPass)
            ->addCompilerPass(new RegisterStoragePass)
            ->addCompilerPass(new RegisterDefinitionCompilerPass);
    }
}
