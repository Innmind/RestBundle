<?php

namespace Innmind\RestBundle\DependencyInjection;

use Innmind\Rest\Server\Configuration as ServerConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $server = new ServerConfiguration;
        $treeBuilder = new TreeBuilder;
        $root = $treeBuilder->root('innmind_rest');

        $root
            ->children()
                ->arrayNode('server')
                    ->append($server->getCollectionNode())
                ->end()
            ->end();

        return $treeBuilder;
    }
}
