<?php

namespace Innmind\RestBundle\EventListener;

use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\Request\Parser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class RequestListener implements EventSubscriberInterface
{
    protected $requestParser;

    public function __construct(Parser $requestParser)
    {
        $this->requestParser = $requestParser;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'determineFormat',
        ];
    }

    /**
     * Verify the content type and accepted one can be understood
     *
     * @param GetResponseEvent $event
     *
     * @throws UnsupportedMediaTypeHttpException If the content type is not supported
     * @throws NotAcceptableHttpException If the wished type is not supported
     *
     * @return void
     */
    public function determineFormat(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RouteKeys::DEFINITION)) {
            return;
        }

        $action = $request->attributes->get(RouteKeys::ACTION);

        if (
            in_array($action, ['create', 'update']) &&
            !$this->requestParser->isContentTypeAcceptable($request)
        ) {
            throw new UnsupportedMediaTypeHttpException;
        }

        if (!$this->requestParser->isRequestedTypeAcceptable($request)) {
            throw new NotAcceptableHttpException;
        }

        $request->setRequestFormat(
            $this->requestParser->getRequestedFormat($request)
        );
    }
}
