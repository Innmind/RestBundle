<?php

namespace Innmind\RestBundle\Tests\EventListener;

use Innmind\RestBundle\EventListener\CapabilitiesResponseListener;
use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Collection;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernel;

class CapabilitiesResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $r;
    protected $k;

    public function setUp()
    {
        $this->l = new CapabilitiesResponseListener(
            new UrlGenerator(
                $c = new RouteCollection,
                new RequestContext
            )
        );

        $def = new Definition('foo');
        $def->setCollection(new Collection('bar'));
        $this->r = new Route('/web/resource/', [RouteKeys::DEFINITION => $def]);
        $c->add('res', $this->r);
        $this->k = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDoesntHandle()
    {
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            new Request,
            HttpKernel::MASTER_REQUEST,
            [
                'res' => $this->r,
            ]
        );
        $this->l->buildResponse($event);
        $this->assertFalse($event->hasResponse());
    }

    public function testBuildResponse()
    {
        $request = new Request;
        $event = new GetResponseForControllerResultEvent(
            $this->k,
            $request,
            HttpKernel::MASTER_REQUEST,
            [
                'res' => $this->r,
            ]
        );
        $request->attributes->set(
            RouteKeys::DEFINITION,
            $this->r->getDefault(RouteKeys::DEFINITION)
        );
        $request->attributes->set(RouteKeys::ACTION, 'capabilities');
        $this->l->buildResponse($event);
        $this->assertSame(
            [
                '</web/resource/>; rel="endpoint"; name="bar_foo"',
            ],
            $event->getResponse()->headers->get('Link', null, false)
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                KernelEvents::VIEW => 'buildResponse',
            ],
            CapabilitiesResponseListener::getSubscribedEvents()
        );
    }
}
