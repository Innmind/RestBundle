<?php

namespace Innmind\RestBundle\Client\Server;

use Innmind\RestBundle\Exception\CapabilitiesException;
use Innmind\Rest\Client\Definition\Loader;
use Innmind\UrlResolver\ResolverInterface;
use GuzzleHttp\Client as Http;
use GuzzleHttp\Message\Response;

class Capabilities
{
    protected $host;
    protected $http;
    protected $cache;
    protected $loader;
    protected $resolver;

    public function __construct(
        $host,
        Http $http,
        CacheInterface $cache,
        Loader $loader,
        ResolverInterface $resolver
    ) {
        $this->host = (string) $host;
        $this->http = $http;
        $this->cache = $cache;
        $this->loader = $loader;
        $this->resolver = $resolver;
    }

    /**
     * Return the definition for the given resource name
     *
     * @param string $name
     *
     * @return \Innmind\Rest\Client\Definition\Resource
     */
    public function get($name)
    {
        if ($this->cache->has($name)) {
            return $this->loader->load($this->cache->get($name));
        }

        if (!$this->cache->isFresh()) {
            $this->refresh();

            return $this->get($name);
        }

        throw new \InvalidArgumentException(sprintf(
            'Unknown resource named "%s" for the host "%s"',
            $name,
            $this->host
        ));
    }

    /**
     * Return all the resource names
     *
     * @return array
     */
    public function keys()
    {
        $names = $this->cache->keys();

        if (empty($names) && !$this->cache->isFresh()) {
            $this->refresh();

            return $this->keys();
        }

        return $names;
    }

    /**
     * Reload all the resource names available for the server
     *
     * @return Capabilities self
     */
    public function refresh()
    {
        $url = $this->resolver->resolve($this->host, '/*');
        $response = $this->http->options($url);

        if (
            $response->getStatusCode() !== 200 ||
            !$response->hasHeader('Link')
        ) {
            throw new CapabilitiesException(sprintf(
                'Couldn\'t retrieve "%s" capabilities',
                $this->host
            ));
        }

        foreach ($this->cache->keys() as $key) {
            $this->cache->remove($key);
        }

        $links = Response::parseHeader($response, 'Link');

        foreach ($links as $link) {
            if (isset($link['rel']) && $link['rel'] === 'endpoint') {
                $url = $this->resolver->resolve(
                    $this->host,
                    substr($link[0], 1, -1)
                );
                $this->cache->save($link['name'], $url);
            }
        }

        return $this;
    }
}
