<?php

namespace Innmind\RestBundle\Tests\DependencyInjection\Compiler;

use Innmind\RestBundle\DependencyInjection\Compiler\RegisterDefinitionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class RegisterDefinitionCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    protected $p;
    protected $b;

    public function setUp()
    {
        $this->p = new RegisterDefinitionCompilerPass;
        $this->b = new ContainerBuilder;
        $loader = new Loader\YamlFileLoader(
            $this->b,
            new FileLocator('Resources/config')
        );
        $loader->load('services.yml');
    }

    public function testRegisterPasses()
    {
        $this->assertSame(
            null,
            $this->p->process($this->b)
        );
        $def = $this->b->getDefinition('innmind_rest.server.definition_compiler');
        $this->assertSame(
            4,
            count($def->getMethodCalls())
        );
        $calls = $def->getMethodCalls();
        $this->assertSame(
            'addCompilerPass',
            $calls[0][0]
        );
        $this->assertInstanceOf(
            Reference::class,
            $calls[0][1][0]
        );
        $this->assertSame(
            'innmind_rest.server.definition_pass.access',
            (string) $calls[0][1][0]
        );
    }
}
