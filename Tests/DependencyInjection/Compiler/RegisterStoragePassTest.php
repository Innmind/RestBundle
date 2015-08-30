<?php

namespace Innmind\RestBundle\Tests\DependencyInjection\Compiler;

use Innmind\RestBundle\DependencyInjection\Compiler\RegisterStoragePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class RegisterStoragePassTest extends \PHPUnit_Framework_TestCase
{
    protected $p;
    protected $b;

    public function setUp()
    {
        $this->p = new RegisterStoragePass;
        $this->b = new ContainerBuilder;
        $loader = new Loader\YamlFileLoader(
            $this->b,
            new FileLocator(__DIR__.'/../../../Resources/config')
        );
        $loader->load('services.yml');
        $def = new DefinitionDecorator('innmind_rest.server.storage.abstract.neo4j');
        $def->addTag('innmind_rest.server.storage', ['name' => 'foo']);
        $this->b->setDefinition('some_storage', $def);
    }

    public function testRegisterStorage()
    {
        $this->assertSame(
            null,
            $this->p->process($this->b)
        );
        $def = $this->b->getDefinition('innmind_rest.server.storages');
        $this->assertSame(
            1,
            count($def->getMethodCalls())
        );
        $calls = $def->getMethodCalls();
        $this->assertSame(
            'add',
            $calls[0][0]
        );
        $this->assertSame(
            'foo',
            $calls[0][1][0]
        );
        $this->assertInstanceOf(
            Reference::class,
            $calls[0][1][1]
        );
        $this->assertSame(
            'some_storage',
            (string) $calls[0][1][1]
        );
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage You must specify a name for the storage some_storage
     */
    public function testThrowIfNoNameSpecified()
    {
        $this->b
            ->getDefinition('some_storage')
            ->clearTags()
            ->addTag('innmind_rest.server.storage');
        $this->p->process($this->b);
    }
}
