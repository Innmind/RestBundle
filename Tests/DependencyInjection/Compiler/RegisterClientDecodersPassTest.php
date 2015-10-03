<?php

namespace Innmind\RestBundle\Tests\DependencyInjection\Compiler;

use Innmind\RestBundle\DependencyInjection\Compiler\RegisterClientDecodersPass;
use Innmind\Rest\Client\Server\Decoder\DelegationDecoder;
use Innmind\Rest\Client\Server\Decoder\CollectionDecoder;
use Innmind\Rest\Client\Server\Decoder\ResourceDecoder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class RegisterClientDecodersPassTest extends \PHPUnit_Framework_TestCase
{
    protected $p;
    protected $b;

    public function setUp()
    {
        $this->p = new RegisterClientDecodersPass;
        $this->b = new ContainerBuilder;
        $loader = new Loader\YamlFileLoader(
            $this->b,
            new FileLocator(__DIR__.'/../../../Resources/config')
        );
        $loader->load('services.yml');
    }

    public function testRegisterDecoders()
    {
        $this->assertSame(
            null,
            $this->p->process($this->b)
        );
        $def = $this->b->getDefinition('innmind_rest.client.decoder.delegation');
        $this->assertSame(2, count($def->getArgument(0)));
        $this->assertSame(
            'innmind_rest.client.decoder.collection',
            (string) $def->getArgument(0)[0]
        );
        $this->assertSame(
            'innmind_rest.client.decoder.resource',
            (string) $def->getArgument(0)[1]
        );

        $this->assertInstanceOf(
            DelegationDecoder::class,
            $del = $this->b->get('innmind_rest.client.decoder.delegation')
        );
        $refl = new \ReflectionObject($del);
        $refl = $refl->getProperty('builders');
        $refl->setAccessible(true);
        $decoders = $refl->getValue($del);
        $refl->setAccessible(false);

        $this->assertInstanceOf(
            CollectionDecoder::class,
            $decoders[0]
        );
        $this->assertInstanceOf(
            ResourceDecoder::class,
            $decoders[1]
        );
    }
}
