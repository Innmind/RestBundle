<?php

namespace Innmind\RestBundle\Tests\EventListener;

use Innmind\RestBundle\EventListener\CapabilitiesResponseListener;
use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Event\ResponseEvent;
use Innmind\Rest\Server\Events;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CapabilitiesResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $r;

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
    }

    public function testDoesntHandle()
    {
        $event = new ResponseEvent(
            new Definition('foo'),
            $rs = new Response,
            new Request,
            [
                'res' => $this->r,
            ],
            'options'
        );
        $this->l->buildResponse($event);
        $this->assertSame(
            [],
            $rs->headers->get('Link', null, false)
        );
    }

    public function testBuildResponse()
    {
        $event = new ResponseEvent(
            new Definition('foo'),
            $rs = new Response,
            $rq = new Request,
            [
                'res' => $this->r,
            ],
            'capabilities'
        );
        $rq->attributes->set(
            RouteKeys::DEFINITION,
            $this->r->getDefault(RouteKeys::DEFINITION)
        );
        $rq->attributes->set(RouteKeys::ACTION, 'capabilities');
        $this->l->buildResponse($event);
        $this->assertSame(
            [
                '</web/resource/>; rel="endpoint"; name="bar_foo"',
            ],
            $rs->headers->get('Link', null, false)
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                Events::RESPONSE => 'buildResponse',
            ],
            CapabilitiesResponseListener::getSubscribedEvents()
        );
    }
}
