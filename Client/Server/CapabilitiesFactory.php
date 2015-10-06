<?php

namespace Innmind\RestBundle\Client\Server;

use Innmind\RestBundle\Client\Server\Cache\FileCache;
use Innmind\RestBundle\Client\LoaderFactory;
use Innmind\UrlResolver\ResolverInterface;
use GuzzleHttp\Client as Http;

class CapabilitiesFactory
{
    protected $instances = [];
    protected $cacheDir;
    protected $resolver;
    protected $http;
    protected $loader;

    public function __construct(
        $cacheDir,
        ResolverInterface $resolver,
        Http $http,
        LoaderFactory $loader
    ) {
        $this->cacheDir = (string) $cacheDir;
        $this->resolver = $resolver;
        $this->http = $http;
        $this->loader = $loader;
    }

    /**
     * Make a capabilities object for a given host
     *
     * @param string $host
     *
     * @return Capabilities
     */
    public function make($host)
    {
        $host = $this->resolver->resolve($host, '/');
        $hash = md5($host);

        if (isset($this->instances[$hash])) {
            return $this->instances[$hash];
        }

        $dir = sprintf(
            '%s/%s',
            rtrim($this->cacheDir, '/'),
            $hash
        );
        $instance = new Capabilities(
            $host,
            $this->http,
            new FileCache(sprintf(
                '%s/capabilities.php',
                $dir
            )),
            $this->loader->make($host),
            $this->resolver
        );
        $this->instances[$hash] = $instance;

        return $instance;
    }
}
