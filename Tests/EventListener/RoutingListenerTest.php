<?php

namespace Innmind\RestBundle\Tests\EventListener;

use Innmind\RestBundle\EventListener\RoutingListener;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\RouteEvent;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutingListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;

    public function setUp()
    {
        $this->l = new RoutingListener('foo/');
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [Events::ROUTE => 'addPrefix'],
            RoutingListener::getSubscribedEvents()
        );
    }

    public function testAddPrefix()
    {
        $event = new RouteEvent(
            new RouteCollection,
            $route = new Route('/coll/res/'),
            $this
                ->getMockBuilder(ResourceDefinition::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
        $this->assertSame(null, $this->l->addPrefix($event));
        $this->assertSame('/foo/coll/res/', $route->getPath());
    }
}
