<?php

namespace Innmind\RestBundle\Client;

use Innmind\RestBundle\Client\Server\Capabilities;
use Innmind\Rest\Client\Client;
use Innmind\Rest\Client\HttpResourceInterface;

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
     * @return HttpResourceInterface|Collection
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
     * @param HttpResourceInterface $resource
     *
     * @return Server self
     */
    public function create($name, HttpResourceInterface $resource)
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
     * @param HttpResourceInterface $resource
     *
     * @return Server self
     */
    public function update($name, $id, HttpResourceInterface $resource)
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
            $definitions[$key] = $this->capabilities->get($key);
        }

        return $definitions;
    }
}
