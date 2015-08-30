<?php

namespace Innmind\RestBundle\Tests\EventListener;

use Innmind\RestBundle\EventListener\ControllerListener;
use Innmind\RestBundle\RouteKeys;
use Innmind\RestBundle\Controller\ResourceController;
use Innmind\Rest\Server\Validator;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Formats;
use Innmind\Rest\Server\Request\Parser;
use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Negotiation\Negotiator;

class ControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;

    public function setUp()
    {
        $this->l = new ControllerListener(
            new Validator(Validation::createValidator()),
            new Parser(
                new Serializer(
                    [new ResourceNormalizer(new ResourceBuilder(
                        PropertyAccess::createPropertyAccessor(),
                        new EventDispatcher
                    ))],
                    [new JsonEncoder]
                ),
                (new Formats)->add('json', 'application/json', 42),
                new Negotiator
            ),
            new EventDispatcher
        );
    }

    public function testInjectDefinition()
    {
        $actions = ['index', 'get', 'delete', 'options'];
        $controller = new ResourceController;
        $kernel = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($actions as $action) {
            $r = new Request;
            $r->attributes->set(
                RouteKeys::DEFINITION,
                $d = new Definition('foo')
            );
            $r->attributes->set(RouteKeys::ACTION, $action);
            $event = new FilterControllerEvent(
                $kernel,
                [$controller, sprintf('%sAction', $action)],
                $r,
                HttpKernel::MASTER_REQUEST
            );
            $this->l->decodeRequest($event);
            $this->assertSame(
                $d,
                $r->attributes->get('definition')
            );
        }

        $r = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/',
                'REQUEST_METHOD' => 'POST',
            ],
            json_encode($expected = ['resource' => []])
        );
        $r->attributes->set(RouteKeys::DEFINITION, $d = new Definition('foo'));
        $r->attributes->set(RouteKeys::ACTION, 'create');
        $r->headers->set('Content-Type', 'application/json');
        $event = new FilterControllerEvent(
            $kernel,
            [$controller, 'createAction'],
            $r,
            HttpKernel::MASTER_REQUEST
        );
        $this->l->decodeRequest($event);
        $this->assertInstanceOf(
            Resource::class,
            $r->attributes->get('resources')
        );
        $this->assertSame(
            $d,
            $r->attributes->get('resources')->getDefinition()
        );

        $r = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/foo',
                'REQUEST_METHOD' => 'PUT',
            ],
            json_encode($expected = ['resource' => []])
        );
        $r->attributes->set(RouteKeys::DEFINITION, $d = new Definition('foo'));
        $r->attributes->set(RouteKeys::ACTION, 'update');
        $r->headers->set('Content-Type', 'application/json');
        $event = new FilterControllerEvent(
            $kernel,
            [$controller, 'updateAction'],
            $r,
            HttpKernel::MASTER_REQUEST
        );
        $this->l->decodeRequest($event);
        $this->assertInstanceOf(
            Resource::class,
            $r->attributes->get('resource')
        );
        $this->assertSame(
            $d,
            $r->attributes->get('resource')->getDefinition()
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\PayloadException
     * @expectedExceptionMessage You can only update one resource at a time
     */
    public function testThrowIfMultipleResourcesToUpdate()
    {
        $controller = new ResourceController;
        $kernel = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $r = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/web/resource/foo',
                'REQUEST_METHOD' => 'PUT',
            ],
            json_encode($expected = ['resources' => []])
        );
        $r->attributes->set(RouteKeys::DEFINITION, $d = new Definition('foo'));
        $r->attributes->set(RouteKeys::ACTION, 'update');
        $r->headers->set('Content-Type', 'application/json');
        $event = new FilterControllerEvent(
            $kernel,
            [$controller, 'updateAction'],
            $r,
            HttpKernel::MASTER_REQUEST
        );
        $this->l->decodeRequest($event);
    }
}
