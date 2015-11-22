<?php

namespace Innmind\RestBundle\Controller;

use Innmind\Rest\Server\Routing\RouteKeys;
use Innmind\Rest\Server\Controller\ResourceController as BaseController;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Registry;
use Symfony\Component\Routing\RouterInterface;

class ResourceController extends BaseController
{
    protected $router;
    protected $registry;

    public function __construct(
        Storages $storages,
        RouterInterface $router,
        Registry $registry
    ) {
        $this->router = $router;
        $this->registry = $registry;

        parent::__construct($storages);
    }

    /**
     * Return all the resources routes
     *
     * @return array
     */
    public function capabilitiesAction()
    {
        $routes = $this->router->getRouteCollection();
        $exposed = [];

        foreach ($routes as $name => $route) {
            if (!$route->hasDefault(RouteKeys::DEFINITION)) {
                continue;
            }

            if (!in_array('OPTIONS', $route->getMethods(), true)) {
                continue;
            }

            $definition = $route->getDefault(RouteKeys::DEFINITION);
            list($collection, $resource) = explode('::', $definition);
            $definition = $this
                ->registry
                ->getCollection($collection)
                ->getResource($resource);

            if ($definition->hasOption('private')) {
                continue;
            }

            $exposed[$name] = $route;
        }

        return $exposed;
    }
}
