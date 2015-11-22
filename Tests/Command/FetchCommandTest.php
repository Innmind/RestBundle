<?php

namespace Innmind\RestBundle\Tests\Command;

use Innmind\RestBundle\Command\FetchCommand;
use Innmind\RestBundle\Client\Server\Capabilities;
use Innmind\RestBundle\Client\Server\CapabilitiesFactory;
use Innmind\RestBundle\Client\LoaderFactory;
use Innmind\Rest\Client\Definition\Loader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FetchCommandTester extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $capFactory = $this
            ->getMockBuilder(CapabilitiesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $capFactory
            ->method('make')
            ->will($this->returnCallback(function($server) {
                $cap = $this
                    ->getMockBuilder(Capabilities::class)
                    ->disableOriginalConstructor()
                    ->getMock();
                $cap
                    ->method('keys')
                    ->willReturn([
                        'foo' => $server . 'foo/',
                    ]);

                return $cap;
            }));
        $loader = $this
            ->getMockBuilder(LoaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loader
            ->method('make')
            ->will($this->returnCallback(function() {
                $loader = $this
                    ->getMockBuilder(Loader::class)
                    ->disableOriginalConstructor()
                    ->getMock();

                return $loader;
            }));

        $container = new ContainerBuilder;
        $container->set('innmind_rest.client.capabilities_factory', $capFactory);
        $container->set('innmind_rest.client.loader_factory', $loader);

        $command = new FetchCommand;
        $command->setContainer($container);

        $app = new Application;
        $app->add($command);
        $c = new CommandTester($app->find('innmind:rest:fetch'));

        $c->execute([
            'command' => $command->getName(),
            'server' => 'http://xn-example.com/',
        ]);

        $this->assertContains(
            'Fetching exposed resources at http://xn-example.com/',
            $c->getDisplay()
        );
        $this->assertContains(
            'Fetching definition for the resource "foo" at "http://xn-example.com/foo/"',
            $c->getDisplay()
        );
        $this->assertContains(
            '<success>All definitions loaded</success>',
            $c->getDisplay()
        );
    }
}
