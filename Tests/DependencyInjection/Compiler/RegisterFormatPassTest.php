<?php

namespace Innmind\RestBundle\Tests\DependencyInjection\Compiler;

use Innmind\RestBundle\DependencyInjection\Compiler\RegisterFormatPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class RegisterFormatPassTest extends \PHPUnit_Framework_TestCase
{
    protected $b;
    protected $p;

    public function setUp()
    {
        $this->b = new ContainerBuilder;
        $loader = new Loader\YamlFileLoader(
            $this->b,
            new FileLocator('Resources/config')
        );
        $loader->load('services.yml');
        $this->p = new RegisterFormatPass;
    }

    public function testRegisterFormats()
    {
        $this->assertSame(
            null,
            $this->p->process($this->b)
        );
        $def = $this->b->getDefinition('innmind_Rest.formats');
        $this->assertSame(
            2,
            count($def->getMethodCalls())
        );
        $calls = $def->getMethodCalls();
        $this->assertSame(
            ['json', 'application/json', 10],
            $calls[0][1]
        );
        $this->assertSame(
            ['form', 'application/x-www-form-urlencoded', 0],
            $calls[1][1]
        );
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage You need to specify a format on the service innmind_rest.encoder.json
     */
    public function testThrowIfNoFormatSpecified()
    {
        $def = $this->b->getDefinition('innmind_rest.encoder.json');
        $def->clearTags();
        $def->addTag('innmind_rest.server.format');

        $this->p->process($this->b);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage You need to specify the associated mime type for json on innmind_rest.encoder.json
     */
    public function testThrowIfNoMimeSpecified()
    {
        $def = $this->b->getDefinition('innmind_rest.encoder.json');
        $def->clearTags();
        $def->addTag('innmind_rest.server.format', ['format' => 'json']);

        $this->p->process($this->b);
    }
}
