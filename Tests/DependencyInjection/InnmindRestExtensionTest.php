<?php

namespace Innmind\RestBundle\Tests\DependencyInjection;

use Innmind\RestBundle\DependencyInjection\InnmindRestExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InnmindRestExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $conf;
    protected $b;

    public function setUp()
    {
        $this->e = new InnmindRestExtension;
        $this->b = new ContainerBuilder;
        $this->conf = [
            'server' => [
                'collections' => [
                    'foo' => [
                        'storage' => 'neo4j',
                    ],
                ],
            ],
        ];
    }

    public function testProcessConfig()
    {
        try {
            $this->e->load([$this->conf], $this->b);
            $this->assertTrue(true, 'Configuration loaded');
        } catch (\Exception $e) {
            $this->fail('Extension load should not throw an exception');
        }
    }
}
