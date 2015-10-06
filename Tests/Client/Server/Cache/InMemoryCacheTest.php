<?php

namespace Innmind\RestBundle\Tests\Client\Server\Cache;

use Innmind\RestBundle\Client\Server\Cache\InMemoryCache;
use Innmind\RestBundle\Client\Server\CacheInterface;

class InMemoryCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $c = new InMemoryCache;

        $this->assertInstanceOf(CacheInterface::class, $c);
    }

    public function testSave()
    {
        $c = new InMemoryCache;

        $this->assertFalse($c->has('foo'));
        $this->assertSame($c, $c->save('foo', 'bar'));
        $this->assertTrue($c->has('foo'));
        $this->assertSame('bar', $c->get('foo'));
        $this->assertSame(['foo'], $c->keys());
    }

    public function testRemove()
    {
        $c = new InMemoryCache;

        $c->save('foo', 'bar');
        $this->assertSame($c, $c->remove('foo'));
        $this->assertFalse($c->has('foo'));
        $this->assertSame([], $c->keys());
    }

    public function testIsFresh()
    {
        $c = new InMemoryCache;

        $this->assertFalse($c->isFresh());
        $c->save('foo', 'bar');
        $this->assertTrue($c->isFresh());
    }
}
