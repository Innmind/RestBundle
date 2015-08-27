<?php

namespace Innmind\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterFormatPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('innmind_rest.server.format');
        $formats = $container->getDefinition('innmind_rest.formats');

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['format'])) {
                    throw new \LogicException(sprintf(
                        'You need to specify a format on the service %s',
                        $id
                    ));
                }

                if (!isset($tag['mime'])) {
                    throw new \LogicException(sprintf(
                        'You need to specify the associated mime type for %s on %s',
                        $tag['format'],
                        $id
                    ));
                }

                $formats->addMethodCall('add', [
                    $tag['format'],
                    $tag['mime'],
                    isset($tag['priority']) ? (int) $tag['priority'] : 0
                ]);
            }
        }
    }
}
