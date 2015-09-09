<?php

namespace Innmind\RestBundle\EventListener;

use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CapabilitiesResponseListener implements EventSubscriberInterface
{
    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::RESPONSE => 'buildResponse',
        ];
    }

    /**
     * Build the response to expose all routes of the API
     *
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function buildResponse(ResponseEvent $event)
    {
        if ($event->getAction() !== 'capabilities') {
            return;
        }

        $routes = $event->getContent();
        $response = $event->getResponse();
        $definition = $event
            ->getRequest()
            ->attributes
            ->get(RouteKeys::DEFINITION);
        $links = $response->headers->get('Link', null, false);

        foreach ($routes as $name => $route) {
            $links[] = sprintf(
                '<%s>; rel="endpoint"; name="%s_%s"',
                $this->urlGenerator->generate($name),
                $definition->getCollection(),
                $definition
            );
        }

        $response->headers->add(['Link' => $links]);
    }
}
