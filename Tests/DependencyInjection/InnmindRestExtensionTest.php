<?php

namespace Innmind\RestBundle\Tests\DependencyInjection;

use Innmind\RestBundle\DependencyInjection\InnmindRestExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class InnmindRestExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $conf;
    protected $b;

    public function setUp()
    {
        $this->e = new InnmindRestExtension;
        $this->b = new ContainerBuilder;
        $loader = new Loader\YamlFileLoader(
            $this->b,
            new FileLocator(__DIR__.'/../../Resources/config')
        );
        $loader->load('services.yml');
        $this->conf = [
            'server' => [
                'collections' => [
                    'foo' => [
                        'storage' => 'neo4j',
                    ],
                ],
            ],
        ];
    }

    public function testProcessConfig()
    {
        try {
            $this->e->load([$this->conf], $this->b);
            $this->assertTrue(true, 'Configuration loaded');
        } catch (\Exception $e) {
            $this->fail('Extension load should not throw an exception');
        }
    }

    public function testLoadCollectionsInRegistry()
    {
        $this->e->load([$this->conf], $this->b);
        $def = $this->b->getDefinition('innmind_rest.server.registry');
        $this->assertSame(
            1,
            count($def->getMethodCalls())
        );
        $call = $def->getMethodCalls()[0];
        $this->assertSame(
            'load',
            $call[0]
        );
        $expected = $this->conf['server']['collections'];
        $expected['foo']['resources'] = [];
        $this->assertSame(
            [['collections' => $expected]],
            $call[1]
        );
    }
}
