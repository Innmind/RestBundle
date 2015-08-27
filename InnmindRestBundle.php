<?php

namespace Innmind\RestBundle;

use Innmind\RestBundle\DependencyInjection\Compiler\RegisterFormatPass;
use Innmind\RestBundle\DependencyInjection\Compiler\RegisterStoragePass;
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
            ->addCompilerPass(new RegisterFormatPass)
            ->addCompilerPass(new RegisterStoragePass);
    }
}
