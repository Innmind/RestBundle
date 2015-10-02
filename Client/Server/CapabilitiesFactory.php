<?php

namespace Innmind\RestBundle\Client\Server;

use Innmind\RestBundle\Client\Server\Cache\FileCache;
use Innmind\Rest\Client\Definition\Loader;
use Innmind\Rest\Client\Definition\Builder;
use Innmind\Rest\Client\Cache\FileCache as DefinitionFileCache;
use Innmind\UrlResolver\ResolverInterface;
use Symfony\Component\Validator\ValidatorInterface;
use GuzzleHttp\Client as Http;

class CapabilitiesFactory
{
    protected $instances = [];
    protected $cacheDir;
    protected $resolver;
    protected $http;
    protected $validator;
    protected $builder;

    public function __construct(
        $cacheDir,
        ResolverInterface $resolver,
        Http $http,
        ValidatorInterface $validator
    ) {
        $this->cacheDir = (string) $cacheDir;
        $this->resolver = $resolver;
        $this->http = $http;
        $this->validator = $validator;
        $this->builder = new Builder;
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
            new Loader(
                new DefinitionFileCache(sprintf(
                    '%s/definitions.php',
                    $dir
                )),
                $this->resolver,
                $this->builder,
                $this->http,
                $this->validator
            ),
            $this->resolver
        );
        $this->instances[$hash] = $instance;

        return $instance;
    }
}
