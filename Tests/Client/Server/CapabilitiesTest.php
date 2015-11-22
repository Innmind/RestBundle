<?php

namespace Innmind\RestBundle\Tests\Client\Server;

use Innmind\RestBundle\Client\Server\Capabilities;
use Innmind\RestBundle\Client\Server\Cache\InMemoryCache;
use Innmind\Rest\Client\Definition\Loader;
use Innmind\Rest\Client\Definition\ResourceDefinition;
use Innmind\Rest\Client\Cache\InMemoryCache as ClientInMemoryCache;
use Innmind\UrlResolver\UrlResolver;
use GuzzleHttp\Client as Http;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class CapabilitiesTest extends \PHPUnit_Framework_TestCase
{
    protected $c;

    public function setUp()
    {
        $resolver = new UrlResolver([]);
        $http = $this
            ->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['options'])
            ->getMock();

        $http
            ->method('options')
            ->will($this->returnCallback(function($url) {
                switch ($url) {
                    case 'http://xn--example.com/*':
                        $response = new Response(
                            200,
                            ['Link' => '</foo/bar/>; rel="endpoint"; name="foo_bar"']
                        );
                        break;
                    case 'http://xn--link.com/*':
                        $response = new Response(200);
                        break;
                    case 'http://xn--http.com/*':
                        $response = new Response(400);
                        break;
                    case 'http://xn--example.com/foo/bar/':
                        $response = new Response(
                            200,
                            ['Content-Type' => 'application/json'],
                            Stream::factory(json_encode([
                                'resource' => [
                                    'id' => 'id',
                                    'properties' => [
                                        'foo' => [
                                            'type' => 'string',
                                            'access' => ['READ', 'CREATE', 'UPDATE'],
                                            'variants' => [],
                                        ],
                                    ],
                                ],
                            ]))
                        );
                        break;
                }

                $response->setEffectiveUrl($url);

                return $response;
            }));

        $this->c = new Capabilities(
            'http://xn--example.com/',
            $http,
            new InMemoryCache,
            new Loader(
                new ClientInMemoryCache,
                $resolver,
                null,
                $http
            ),
            $resolver
        );
    }

    public function testGetResourceDefinition()
    {
        $def = $this->c->get('foo_bar');

        $this->assertInstanceOf(ResourceDefinition::class, $def);
        $this->assertSame($def, $this->c->get('foo_bar'));

        $this->assertSame(['foo_bar'], $this->c->keys());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown resource named "foo_foo" for the host "http://xn--example.com/"
     */
    public function testThrowIfResourceNotFound()
    {
        $this->c->get('foo_foo');
    }

    /**
     * @expectedException Innmind\RestBundle\Exception\CapabilitiesException
     * @expectedExceptionMessage Couldn't retrieve "http://xn--http.com/" capabilities
     */
    public function testThrowIfFailedLoadingCapabilities()
    {
        $refl = new \ReflectionObject($this->c);
        $refl = $refl->getProperty('host');
        $refl->setAccessible(true);
        $refl->setValue($this->c, 'http://xn--http.com/');
        $refl->setAccessible(false);

        $this->c->refresh();
    }

    /**
     * @expectedException Innmind\RestBundle\Exception\CapabilitiesException
     * @expectedExceptionMessage Couldn't retrieve "http://xn--link.com/" capabilities
     */
    public function testThrowIfNoEndpointFound()
    {
        $refl = new \ReflectionObject($this->c);
        $refl = $refl->getProperty('host');
        $refl->setAccessible(true);
        $refl->setValue($this->c, 'http://xn--link.com/');
        $refl->setAccessible(false);

        $this->c->refresh();
    }
}
