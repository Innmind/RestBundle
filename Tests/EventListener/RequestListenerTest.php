<?php

namespace Innmind\RestBundle\Tests\EventListener;

use Innmind\RestBundle\EventListener\RequestListener;
use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\Formats;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Request\Parser;
use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Negotiation\Negotiator;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $kernel;

    public function setUp()
    {
        $formats = new Formats;
        $formats->add('json', 'application/json', 42);
        $parser = new Parser(
            new Serializer(
                [new ResourceNormalizer(new ResourceBuilder(
                    PropertyAccess::createPropertyAccessor(),
                    new EventDispatcher
                ))],
                [new JsonEncoder]
            ),
            $formats,
            new Negotiator
        );

        $this->l = new RequestListener($parser);
        $this->kernel = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDoesntHandle()
    {
        $request = new Request;
        $event = new GetResponseEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $defaultFormat = $request->getRequestFormat();
        $this->assertSame(
            null,
            $this->l->determineFormat($event)
        );
        $this->assertSame(
            $defaultFormat,
            $request->getRequestFormat()
        );
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
     */
    public function testThrowIfUnknownContentType()
    {
        $request = new Request;
        $request->attributes->set(RouteKeys::DEFINITION, new Definition('foo'));
        $request->attributes->set(RouteKeys::ACTION, 'create');
        $request->headers->set('Content-Type', 'text/html');
        $event = new GetResponseEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $defaultFormat = $request->getRequestFormat();
        $this->l->determineFormat($event);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException
     */
    public function testThrowIfUnknownAcceptType()
    {
        $request = new Request;
        $request->attributes->set(RouteKeys::DEFINITION, new Definition('foo'));
        $request->attributes->set(RouteKeys::ACTION, 'create');
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'text/xml');
        $event = new GetResponseEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $defaultFormat = $request->getRequestFormat();
        $this->l->determineFormat($event);
    }

    public function testDetermineFormat()
    {
        $request = new Request;
        $request->attributes->set(RouteKeys::DEFINITION, new Definition('foo'));
        $request->attributes->set(RouteKeys::ACTION, 'create');
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'application/json');
        $event = new GetResponseEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $defaultFormat = $request->getRequestFormat();
        $this->l->determineFormat($event);
        $this->assertSame(
            'json',
            $request->getRequestFormat()
        );
    }
}
