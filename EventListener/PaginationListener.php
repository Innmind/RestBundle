<?php

namespace Innmind\RestBundle\EventListener;

use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\EventListener\PaginationListener as ServerPaginationListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginationListener extends ServerPaginationListener
{
    protected $requestStack;
    protected $urlGenerator;

    public function __construct(
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = parent::getSubscribedEvents();
        unset($events[Events::REQUEST]);

        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function canPaginate()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$request->attributes->has(RouteKeys::DEFINITION)) {
            return false;
        }

        $definition = $request->attributes->get(RouteKeys::DEFINITION);

        if (!$request->query->has('limit')) {
            if ($definition->hasOption('paginate')) {
                return true;
            }
            return false;
        }

        $offset = $request->query->get('offset', 0);
        $limit = $request->query->get('limit');

        if (!is_numeric($offset) || !is_numeric($limit)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationBounds()
    {
        $request = $this->requestStack->getCurrentRequest();
        $definition = $request->attributes->get(RouteKeys::DEFINITION);

        $offset = (int) $request->query->get('offset', 0);
        $limit = $request->query->has('limit') ?
            (int) $request->query->get('limit') :
            (int) $definition->getOption('paginate');

        return [$offset, $limit];
    }
}
