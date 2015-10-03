<?php

namespace Innmind\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterClientDecodersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $delegation = $container->getDefinition('innmind_rest.client.decoder.delegation');
        $ids = $container->findTaggedServiceIds('innmind_rest.client.decoder');
        $decoders = [];

        foreach ($ids as $id => $tags) {
            $decoders[] = new Reference($id);
        }

        $delegation->replaceArgument(0, $decoders);
    }
}
