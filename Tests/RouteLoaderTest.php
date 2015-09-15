<?php

namespace Innmind\RestBundle\Tests;

use Innmind\RestBundle\RouteLoader;
use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\RouteLoader as ServerRouteLoader;
use Innmind\Rest\Server\Registry;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RouteLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $rl;

    public function setUp()
    {
        $registry = new Registry;
        $registry->load([
            'collections' => [
                'web' => [
                    'storage' => 'neo4j',
                    'resources' => [
                        'resource' => [
                            'storage' => 'neo4j',
                            'id' => 'uuid',
                            'properties' => [
                                'uuid' => [
                                    'type' => 'string',
                                    'access' => ['READ'],
                                    'options' => [],
                                ]
                            ],
                            'options' => [],
                            'meta' => [],
                        ],
                    ],
                ],
            ],
        ]);
        $server = new ServerRouteLoader(
            new EventDispatcher,
            $registry
        );

        $this->rl = new RouteLoader($server);
    }

    public function testHasControllerSet()
    {
        $routes = $this->rl->load('.');

        foreach ($routes->getIterator() as $route) {
            $this->assertTrue($route->hasDefault('_controller'));
            $this->assertSame(
                sprintf(
                    'InnmindRestBundle:Resource:%s',
                    $route->getDefault(RouteKeys::ACTION)
                ),
                $route->getDefault('_controller')
            );
        }
    }

    public function testHasCapabilitiesRoute()
    {
        $routes = $this->rl->load('.');

        $this->assertTrue(isset($routes->all()['innmind_rest_server_capabilities']));
        $route = $routes->all()['innmind_rest_server_capabilities'];
        $this->assertSame(
            '/*',
            $route->getPath()
        );
        $this->assertSame(
            ['OPTIONS'],
            $route->getMethods()
        );
    }

    /**
     * @expectedException LogicException
     * @expectExceptionMessage Do not add the "innmind_rest" loader twice
     */
    public function testThrowIfLoadedTwice()
    {
        $this->rl->load('.');
        $this->rl->load('.');
    }

    public function testGetRoutes()
    {
        $this->rl->load('.');

        $this->assertSame(
            1,
            count($this->rl->getRoutes('OPTIONS'))
        );
        $this->assertSame(
            6,
            count($this->rl->getRoutes())
        );
    }
}
