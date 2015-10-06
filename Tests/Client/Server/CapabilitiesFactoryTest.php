<?php

namespace Innmind\RestBundle\Tests\Client\Server;

use Innmind\RestBundle\Client\Server\CapabilitiesFactory;
use Innmind\RestBundle\Client\Server\Capabilities;
use Innmind\RestBundle\Client\LoaderFactory;
use Innmind\UrlResolver\UrlResolver;
use Symfony\Component\Validator\Validation;
use GuzzleHttp\Client as Http;

class CapabilitiesFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $f;

    public function setUp()
    {
        $this->f = new CapabilitiesFactory(
            sys_get_temp_dir() . '/cap_factory/',
            $r = new UrlResolver([]),
            $h = new Http,
            new LoaderFactory(
                sys_get_temp_dir() . '/cap_factory/',
                $r,
                $h,
                Validation::createValidator()
            )
        );
    }

    public function testMake()
    {
        $cap = $this->f->make('http://xn--example.com');

        $this->assertInstanceOf(Capabilities::class, $cap);
        unset($this->f);
        unset($cap);

        $folder = sys_get_temp_dir() . '/cap_factory/' . md5('http://xn--example.com/');
        $this->assertTrue(file_exists($folder . '/capabilities.php'));
        $this->assertTrue(file_exists($folder . '/definitions.php'));
    }

    public function testMakeOnce()
    {
        $cap = $this->f->make('http://xn--example.com');

        $this->assertSame($cap, $this->f->make('http://xn--example.com'));
    }
}
