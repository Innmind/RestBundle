<?php

namespace Innmind\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterStoragePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('innmind_rest.server.storage');
        $def = $container->getDefinition('innmind_rest.server.storages');

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['alias'])) {
                    throw new \LogicException(sprintf(
                        'You must specify an alias for the storage %s',
                        $id
                    ));
                }

                $def->addMethodCall('add', [$tag['alias'], new Reference($id)]);
            }
        }
    }
}
