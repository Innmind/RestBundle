<?php

namespace Innmind\RestBundle\Tests;

use Innmind\RestBundle\Client;
use Innmind\RestBundle\Client\ServerFactory;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testServer()
    {
        $factory = $this
            ->getMockBuilder(ServerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $server = $this
            ->getMockBuilder(Server::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory
            ->method('make')
            ->willReturn($server);

        $client = new Client($factory);

        $this->assertInstanceOf(Server::class, $client->server('foo'));
        $this->assertSame(
            $client->server('foo'),
            $client->server('foo')
        );
    }
}
