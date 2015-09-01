<?php

namespace Innmind\RestBundle\Tests\EventListener;

use Innmind\RestBundle\EventListener\PaginationListener;
use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

class PaginationListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $l;
    protected $rs;

    public function setUp()
    {
        $this->l = new PaginationListener(
            $this->rs = new RequestStack,
            $this
                ->getMockBuilder(UrlGenerator::class)
                ->disableOriginalConstructor()
                ->getMock(),
            $this
                ->getMockBuilder(RouteLoader::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    public function testCanPaginate()
    {
        $d = new Definition('foo');
        $r = new Request([
            'limit' => '42',
            'offset' => '42',
        ]);
        $r->attributes->set(RouteKeys::DEFINITION, $d);
        $this->rs->push($r);

        $this->assertTrue($this->l->canPaginate());

        $this->rs->pop();
        $r = new Request;
        $r->attributes->set(RouteKeys::DEFINITION, $d);
        $d->addOption('paginate', 42);
        $this->rs->push($r);

        $this->assertTrue($this->l->canPaginate());
    }

    public function testCantPaginate()
    {
        $this->assertFalse($this->l->canPaginate());

        $r = new Request([
            'limit' => 42,
            'offset' => 42,
        ]);
        $this->rs->push($r);

        $this->assertFalse($this->l->canPaginate());

        $this->rs->pop();
        $d = new Definition('foo');
        $r = new Request;
        $r->attributes->set(RouteKeys::DEFINITION, $d);
        $this->rs->push($r);

        $this->assertFalse($this->l->canPaginate());

        $this->rs->pop();
        $r = new Request([
            'limit' => 42,
            'offset' => 'foo',
        ]);
        $r->attributes->set(RouteKeys::DEFINITION, $d);

        $this->assertFalse($this->l->canPaginate());
    }

    public function testGetPaginationBounds()
    {
        $r = new Request([
            'limit' => '42',
            'offset' => '42',
        ]);
        $this->rs->push($r);

        $this->assertSame(
            [42, 42],
            $this->l->getPaginationBounds()
        );

        $this->rs->pop();
        $r = new Request;
        $d = new Definition('foo');
        $d->addOption('paginate', '42');
        $r->attributes->set(RouteKeys::DEFINITION, $d);
        $this->rs->push($r);

        $this->assertSame(
            [0, 42],
            $this->l->getPaginationBounds()
        );
    }
}
