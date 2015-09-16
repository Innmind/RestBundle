<?php

namespace Innmind\RestBundle\Controller;

use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Exception\ResourceNotFoundException;
use Innmind\Rest\Server\Exception\TooManyResourcesFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResourceController extends Controller
{
    /**
     * Expose the list of resources for the given resource definition
     *
     * @param Definition $definition
     *
     * @return Collection
     */
    public function indexAction(Definition $definition)
    {
        return $this
            ->get('innmind_rest.server.storages')
            ->get($definition->getStorage())
            ->read($definition);
    }

    /**
     * Return a single resource
     *
     * @param Definition $definition
     * @param string $id
     *
     * @return Resource
     */
    public function getAction(Definition $definition, $id)
    {
        $storage = $this
            ->get('innmind_rest.server.storages')
            ->get($definition->getStorage());
        $resources = $storage->read($definition, $id);

        if ($resources->count() < 1) {
            throw new ResourceNotFoundException;
        } else if ($resources->count() > 1) {
            throw new TooManyResourcesFoundException;
        }

        return $resources->current();
    }

    /**
     * Create a resource
     *
     * @param Innmind\Rest\Resource|Collection $resources
     *
     * @return Innmind\Rest\Resource|Collection
     */
    public function createAction($resources)
    {
        if ($resources instanceof Collection) {
            foreach ($resources as $resource) {
                $this->createAction($resource);
            }
        } else {
            $storage = $this
                ->get('innmind_rest.server.storages')
                ->get($resources->getDefinition()->getStorage());
            $id = $storage->create($resources);
            $resources->set(
                $resources->getDefinition()->getId(),
                $id
            );
        }

        return $resources;
    }

    /**
     * Update a resource
     *
     * @param Innmind\Rest\Server\Resource $resource
     * @param string $id
     *
     * @return Innmind\Rest\Server\Resource
     */
    public function updateAction(Resource $resource, $id)
    {
        $this
            ->get('innmind_rest.server.storages')
            ->get($resource->getDefinition()->getStorage())
            ->update($resource, $id);

        return $resource;
    }

    /**
     * Delete a resource
     *
     * @param Definition $definition
     * @param string $id
     *
     * @return void
     */
    public function deleteAction(Definition $definition, $id)
    {
        $this
            ->get('innmind_rest.server.storages')
            ->get($definition->getStorage())
            ->delete($definition, $id);
    }

    /**
     * Format the resource description for the outside world
     * without exposing sensitive data
     *
     * @param Definition $definition
     *
     * @return array
     */
    public function optionsAction(Definition $definition)
    {
        $output = [
            'id' => $definition->getId(),
            'properties' => [],
        ];

        foreach ($definition->getproperties() as $property) {
            $output['properties'][(string) $property] = [
                'type' => $property->getType(),
                'access' => $property->getAccess(),
                'variants' => $property->getVariants(),
            ];

            if ($property->getType() === 'array') {
                $output['properties'][(string) $property]['inner_type'] = $property->getOption('inner_type');
            }

            if ($property->hasOption('optional')) {
                $output['properties'][(string) $property]['optional'] = true;
            }

            if ($property->containsResource()) {
                $sub = $property->getOption('resource');
                $output['properties'][(string) $property]['resource'] = $sub;
            }
        }

        if ($metas = $definition->getMetas()) {
            $output['meta'] = $metas;
        }

        return ['resource' => $output];
    }

    /**
     * Return all the resources routes
     *
     * @return array
     */
    public function capabilitiesAction()
    {
        $routes = $this
            ->get('router')
            ->getRouteCollection();
        $registry = $this->get('innmind_rest.server.registry');
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
            $definition = $registry
                ->getCollection($collection)
                ->getResource($resource);
            $route->setDefault(RouteKeys::DEFINITION, $definition);

            if ($definition->hasOption('private')) {
                continue;
            }

            $exposed[$name] = $route;
        }

        return $exposed;
    }
}
