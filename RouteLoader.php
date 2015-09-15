<?php

namespace Innmind\RestBundle;

use Innmind\Rest\Server\RouteLoader as ServerRouteLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    protected $loader;
    protected $loaded = false;
    protected $routes;

    public function __construct(ServerRouteLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if ($this->loaded === true) {
            throw new \LogicException(
                'Do not add the "innmind_rest" loader twice'
            );
        }

        $routes = $this->loader->load($resource, $type);
        $iterator = $routes->getIterator();

        foreach ($iterator as $route) {
            $route->setDefault(
                '_controller',
                sprintf(
                    'InnmindRestBundle:Resource:%s',
                    $route->getDefault(RouteKeys::ACTION)
                )
            );
        }

        $serverRoutes = new RouteCollection;
        $serverRoutes->addCollection($routes);

        $capabilities = new Route('/*');
        $capabilities
            ->setMethods('OPTIONS')
            ->setDefault(
                '_controller',
                'InnmindRestBundle:Resource:capabilities'
            )
            ->setDefault(
                RouteKeys::ACTION,
                'capabilities'
            );
        $serverRoutes->add('innmind_rest_server_capabilities', $capabilities);

        $this->loaded = true;
        $this->routes = $routes;

        return $serverRoutes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $this->loader->supports($resource, $type);
    }

    /**
     * Return all resources routes for the given method
     *
     * @param string $method
     *
     * @return array
     */
    public function getRoutes($method = null)
    {
        $routes = [];

        foreach ($this->routes as $name => $route) {
            if (
                in_array((string) $method, $route->getMethods(), true) ||
                $method === null
            ) {
                $routes[$name] = $route;
            }
        }

        return $routes;
    }
}
