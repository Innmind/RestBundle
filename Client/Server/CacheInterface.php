<?php

namespace Innmind\RestBundle\Client\Server;

/**
 * Cache for the routes exposed in the capabilities route of a server
 */
interface CacheInterface
{
    /**
     * Save a couple name/url for a resource
     *
     * @param string $name Resource name
     * @param string $url Where the resource can be found
     *
     * @return CacheInterface self
     */
    public function save($name, $url);

    /**
     * Check if the given resource name exist
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Return all the resource names
     *
     * @return array
     */
    public function keys();

    /**
     * Return the url for the given resource name
     *
     * @param string $name
     *
     * @return string
     */
    public function get($name);

    /**
     * Remove a couple name/url
     *
     * @param string $name
     *
     * @return CacheInterface self
     */
    public function remove($name);

    /**
     * Check if the cache is considered fresh
     *
     * Meaning, does the info has been retrieved from the server in this process
     *
     * @return bool
     */
    public function isFresh();
}
