<?php

namespace Innmind\RestBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class InnmindRestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        $registry = $container->getDefinition('innmind_rest.server.registry');
        $registry->addMethodCall(
            'load',
            [['collections' => $config['server']['collections']]]
        );

        if ($config['server']['prefix'] !== null) {
            $container
                ->getDefinition('innmind_rest.server.route_loader')
                ->replaceArgument(2, $config['server']['prefix']);
        }
    }
}
