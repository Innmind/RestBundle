<?php

namespace Innmind\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterDefinitionCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(
            'innmind_rest.server.definition.pass'
        );
        $def = $container->getDefinition(
            'innmind_rest.server.definition_compiler'
        );

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag) {
                $def->addMethodCall('addCompilerPass', [new Reference($id)]);
            }
        }
    }
}
