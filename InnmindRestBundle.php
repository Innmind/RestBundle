<?php

namespace Innmind\RestBundle;

use Innmind\RestBundle\DependencyInjection\Compiler\RegisterClientDecodersPass;
use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterFormatPass;
use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterStoragePass;
use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterDefinitionCompilerPass;
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
            ->addCompilerPass(new RegisterFormatPass(
                'innmind_rest.server.formats',
                'innmind_rest.server.format'
            ))
            ->addCompilerPass(new RegisterStoragePass(
                'innmind_rest.server.storages',
                'innmind_rest.server.storage'
            ))
            ->addCompilerPass(new RegisterDefinitionCompilerPass(
                'innmind_rest.server.definition_compiler',
                'innmind_rest.server.definition_pass'
            ));
    }
}
