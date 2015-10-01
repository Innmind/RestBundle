<?php

namespace Innmind\RestBundle\Tests\Client\Server\Cache;

use Innmind\RestBundle\Client\Server\Cache\FileCache;
use Innmind\RestBundle\Client\Server\CacheInterface;

class FileCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $file;

    public function setUp()
    {
        $this->file = sys_get_temp_dir() . '/capabilitiesCache.php';
    }

    public function testInterface()
    {
        $c = new FileCache($this->file);

        $this->assertInstanceOf(CacheInterface::class, $c);
        unset($c);
        unlink($this->file);
    }

    public function testContentReloading()
    {
        $c = new FileCache($this->file);

        $this->assertTrue($c->isFresh());
        $c->save('foo', 'bar');
        unset($c);

        $c2 = new FileCache($this->file);

        $this->assertFalse($c2->isFresh());
        $this->assertTrue($c2->has('foo'));
        $this->assertSame('bar', $c2->get('foo'));
        $c2->save('foo', 'baz');
        $this->assertTrue($c2->isFresh());
        unset($c2);

        $c3 = new FileCache($this->file);
        $c3->remove('foo');
        $this->assertTrue($c3->isFresh());
        unset($c3);

        $c4 = new FileCache($this->file);
        $this->assertSame([], $c4->keys());
    }
}
