<?php

namespace Innmind\RestBundle\Client;

use Innmind\RestBundle\Client\Server\Capabilities;
use Innmind\Rest\Client\Client;
use Innmind\Rest\Client\Resource;

class Server
{
    protected $capabilities;
    protected $client;

    public function __construct(
        Capabilities $capabilities,
        Client $client
    ) {
        $this->capabilities = $capabilities;
        $this->client = $client;
    }

    /**
     * Read resource(s) from a server
     *
     * @param string $name Resource name
     * @param mixed $id
     *
     * @return \Innmind\Rest\Client\Server\Resource|\Innmind\Rest\Client\Server\Collection
     */
    public function read($name, $id = null)
    {
        $definition = $this->capabilities->get($name);

        if ($id !== null) {
            $url = $definition->getUrl() . (string) $id;
        } else {
            $url = $definition->getUrl();
        }

        return $this->client->read($url);
    }

    /**
     * Create a new resource
     *
     * @param string $name
     * @param \Innmind\Rest\Client\Resource $resource
     *
     * @return Server self
     */
    public function create($name, Resource $resource)
    {
        $this->client->create(
            $this->capabilities->get($name)->getUrl(),
            $resource
        );

        return $this;
    }

    /**
     * Update a resource
     *
     * @param string $name
     * @param mixed $id
     * @param \Innmind\Rest\Client\Resource $resource
     *
     * @return Server self
     */
    public function update($name, $id, Resource $resource)
    {
        $definition = $this->capabilities->get($name);
        $this->client->update(
            $definition->getUrl() . (string) $id,
            $resource
        );

        return $this;
    }

    /**
     * Delete a resource
     *
     * @param string $name
     * @param mixed $id
     *
     * @return Server self
     */
    public function remove($name, $id)
    {
        $this->client->remove(
            $this->capabilities->get($name)->getUrl() . (string) $id
        );

        return $this;
    }

    /**
     * Return the list of resource definitions exposed via the capabilities route
     *
     * @return array
     */
    public function resources()
    {
        $keys = $this->capabilities->keys();
        $definitions = [];

        foreach ($keys as $key) {
            $definitions[] = $this->capabilities->get($key);
        }

        return $definitions;
    }
}
