<?php

namespace Innmind\RestBundle\Tests\EventListener;

use Innmind\RestBundle\EventListener\ExceptionListener;
use Innmind\Rest\Server\Exception\PayloadException;
use Innmind\Rest\Server\Exception\ValidationException;
use Innmind\Rest\Server\Exception\ResourceNotFoundException;
use Innmind\Rest\Server\Exception\TooManyResourcesFoundException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpFoundation\Request;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $kernel;

    public function setUp()
    {
        $this->kernel = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::EXCEPTION => 'buildHttpException'],
            ExceptionListener::getSubscribedEvents()
        );
    }

    public function testBuildBadRequest()
    {
        $ev = new GetResponseForExceptionEvent(
            $this->kernel,
            new Request,
            HttpKernel::MASTER_REQUEST,
            new PayloadException
        );
        (new ExceptionListener)->buildHttpException($ev);
        $this->assertInstanceOf(
            BadRequestHttpException::class,
            $ev->getException()
        );

        $ev->setException(new ValidationException);
        (new ExceptionListener)->buildHttpException($ev);
        $this->assertInstanceOf(
            BadRequestHttpException::class,
            $ev->getException()
        );
    }

    public function testBuildNotFound()
    {
        $ev = new GetResponseForExceptionEvent(
            $this->kernel,
            new Request,
            HttpKernel::MASTER_REQUEST,
            new ResourceNotFoundException
        );
        (new ExceptionListener)->buildHttpException($ev);
        $this->assertInstanceOf(
            NotFoundHttpException::class,
            $ev->getException()
        );
    }

    public function testBuildConflict()
    {
        $ev = new GetResponseForExceptionEvent(
            $this->kernel,
            new Request,
            HttpKernel::MASTER_REQUEST,
            new TooManyResourcesFoundException
        );
        (new ExceptionListener)->buildHttpException($ev);
        $this->assertInstanceOf(
            ConflictHttpException::class,
            $ev->getException()
        );
    }
}
