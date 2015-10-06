<?php

namespace Innmind\RestBundle\Tests\Client;

use Innmind\RestBundle\Client\Server;
use Innmind\RestBundle\Client\Server\Capabilities;
use Innmind\Rest\Client\Client;
use Innmind\Rest\Client\Definition\Resource as Definition;
use Innmind\Rest\Client\Server\Resource;
use Innmind\Rest\Client\Server\Collection;
use Innmind\Rest\Client\Resource as ClientResource;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    protected $s;

    public function setUp()
    {
        $capabilities = $this
            ->getMockBuilder(Capabilities::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'keys'])
            ->getMock();
        $client = $this
            ->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['read', 'create', 'update', 'remove'])
            ->getMock();
        $def = $this
            ->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $capabilities
            ->method('keys')
            ->willReturn(['foo']);
        $capabilities
            ->method('get')
            ->willReturn($def);

        $client
            ->method('read')
            ->will($this->returnCallback(function($url) {
                if (substr($url, -2) === '42') {
                    return $this
                        ->getMockBuilder(Resource::class)
                        ->disableOriginalConstructor()
                        ->getMock();
                } else {
                    return $this
                        ->getMockBuilder(Collection::class)
                        ->disableOriginalConstructor()
                        ->getMock();
                }
            }));
        $client
            ->method('create')
            ->willReturn($client);
        $client
            ->method('update')
            ->willReturn($client);
        $client
            ->method('remove')
            ->willReturn($client);

        $this->s = new Server(
            $capabilities,
            $client
        );
    }

    public function testResources()
    {
        $resources = $this->s->resources();

        $this->assertSame(1, count($resources));
        $this->assertInstanceOf(Definition::class, $resources['foo']);
    }

    public function testRead()
    {
        $resource = $this->s->read('foo', 42);
        $this->assertInstanceOf(Resource::class, $resource);

        $collection = $this->s->read('foo');
        $this->assertInstanceOf(Collection::class, $collection);
    }

    public function testCreate()
    {
        $this->assertSame(
            $this->s,
            $this->s->create('foo', new ClientResource)
        );
    }

    public function testUpdate()
    {
        $this->assertSame(
            $this->s,
            $this->s->update('foo', 42, new ClientResource)
        );
    }

    public function testRemove()
    {
        $this->assertSame(
            $this->s,
            $this->s->remove('foo', 42)
        );
    }
}
