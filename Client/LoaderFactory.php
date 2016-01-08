<?php

namespace Innmind\RestBundle\Client;

use Innmind\Rest\Client\Definition\Loader;
use Innmind\Rest\Client\Definition\Builder;
use Innmind\Rest\Client\Cache\FileCache;
use Innmind\UrlResolver\ResolverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use GuzzleHttp\Client as Http;

class LoaderFactory
{
    protected $instances = [];
    protected $cacheDir;
    protected $resolver;
    protected $builder;
    protected $http;
    protected $validator;

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
     * Create a loader for a given host
     *
     * @param string $host
     *
     * @return Loader
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
        $instance = new Loader(
            new FileCache(sprintf(
                '%s/definitions.php',
                $dir
            )),
            $this->resolver,
            $this->builder,
            $this->http,
            $this->validator
        );
        $this->instances[$hash] = $instance;

        return $instance;
    }
}
