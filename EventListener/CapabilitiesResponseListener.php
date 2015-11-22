<?php

namespace Innmind\RestBundle\EventListener;

use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Registry;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

class CapabilitiesResponseListener implements EventSubscriberInterface
{
    protected $urlGenerator;
    protected $registry;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Registry $registry
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'buildResponse',
        ];
    }

    /**
     * Build the response to expose all routes of the API
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @return void
     */
    public function buildResponse(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (
            !$request->attributes->has(RouteKeys::ACTION) ||
            $request->attributes->get(RouteKeys::ACTION) !== 'capabilities'
        ) {
            return;
        }

        $routes = $event->getControllerResult();
        $response = new Response;
        $links = $response->headers->get('Link', null, false);

        foreach ($routes as $name => $route) {
            $definition = $route->getDefault(RouteKeys::DEFINITION);
            list($collection, $resource) = explode('::', $definition);
            $definition = $this
                ->registry
                ->getCollection($collection)
                ->getResource($resource);

            $links[] = sprintf(
                '<%s>; rel="endpoint"; name="%s_%s"',
                $this->urlGenerator->generate($name),
                $definition->getCollection(),
                $definition
            );
        }

        $response->headers->add(['Link' => $links]);
        $event->setResponse($response);
    }
}
