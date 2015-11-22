<?php

namespace Innmind\RestBundle\EventListener;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\RouteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoutingListener implements EventSubscriberInterface
{
    protected $prefix;

    public function __construct($prefix)
    {
        $this->prefix = sprintf(
            '/%s',
            ltrim(rtrim($prefix, '/'), '/')
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ROUTE => 'addPrefix',
        ];
    }

    /**
     * Add a prefix on each route
     *
     * @param RouteEvent $event
     *
     * @return void
     */
    public function addPrefix(RouteEvent $event)
    {
        $route = $event->getRoute();

        $route->setPath($this->prefix . $route->getPath());
    }
}
