<?php

namespace Innmind\RestBundle\Tests\Client;

use Innmind\RestBundle\Client\LoaderFactory;
use Innmind\Rest\Client\Definition\Loader;
use Innmind\UrlResolver\UrlResolver;
use Symfony\Component\Validator\Validation;
use GuzzleHttp\Client as Http;

class LoaderFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $f;

    public function setUp()
    {
        $this->f = new LoaderFactory(
            sys_get_temp_dir() . '/loader_factory/',
            new UrlResolver([]),
            new Http,
            Validation::createValidator()
        );
    }

    public function testMake()
    {
        $loader = $this->f->make('http://xn--loader.com');

        $this->assertInstanceOf(Loader::class, $loader);
        $this->assertSame($loader, $this->f->make('http://xn--loader.com'));
    }
}
