<?php

namespace Innmind\RestBundle\Tests\Client;

use Innmind\RestBundle\Client\ServerFactory;
use Innmind\RestBundle\Client\Server;
use Innmind\RestBundle\Client\LoaderFactory;
use Innmind\RestBundle\Client\Server\CapabilitiesFactory;
use Innmind\UrlResolver\UrlResolver;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use GuzzleHttp\Client as Http;

class ServerFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $f;

    public function setUp()
    {
        $cache = sys_get_temp_dir() . '/server_fatory/';
        $http = new Http;
        $resolver = new UrlResolver([]);
        $validator = Validation::createValidator();
        $loader = new LoaderFactory(
            $cache,
            $resolver,
            $http,
            $validator
        );
        $capabilities = new CapabilitiesFactory(
            $cache,
            $resolver,
            $http,
            $loader
        );

        $this->f = new ServerFactory(
            $resolver,
            $capabilities,
            $loader,
            new Serializer([], []),
            $validator,
            new EventDispatcher,
            $http
        );
    }

    public function testMake()
    {
        $server = $this->f->make('http://xn--serv-factory.com');

        $this->assertInstanceOf(Server::class, $server);
        $this->assertSame($server, $this->f->make('http://xn--serv-factory.com'));
    }
}
