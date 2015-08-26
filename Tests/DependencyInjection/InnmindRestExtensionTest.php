<?php

namespace Innmind\RestBundle\Tests\DependencyInjection;

use Innmind\RestBundle\DependencyInjection\InnmindRestExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InnmindRestExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $e;

    public function setUp()
    {
        $this->e = new InnmindRestExtension;
    }

    public function testProcessConfig()
    {
        $b = new ContainerBuilder;
        $conf = [
            'server' => [
                'collections' => [
                    'foo' => [
                        'storage' => 'neo4j',
                    ],
                ],
            ],
        ];

        try {
            $this->e->load([$conf], $b);
            $this->assertTrue(true, 'Configuration loaded');
        } catch (\Exception $e) {
            throw $e;
            $this->fail('Extension load should not throw an exception');
        }
    }
}
