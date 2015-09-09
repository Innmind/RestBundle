<?php

namespace Innmind\RestBundle\Tests;

use Innmind\RestBundle\InnmindRestBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InnmindRestBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testCompilerPassesAreLoaded()
    {
        $b = new InnmindRestBundle;
        $this->assertSame(
            null,
            $b->build($c = new ContainerBuilder)
        );
        $conf = $c->getCompiler()->getPassConfig();
        $this->assertSame(
            3,
            count($conf->getBeforeOptimizationPasses())
        );
    }
}
