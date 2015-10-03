<?php

namespace Innmind\RestBundle\Client;

use Innmind\RestBundle\Client\Server\CapabilitiesFactory;
use Innmind\Rest\Client\Client;
use Innmind\Rest\Client\Validator;
use Innmind\Rest\Client\Serializer\Normalizer\ResourceNormalizer;
use Innmind\UrlResolver\ResolverInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use GuzzleHttp\Client as Http;

class ServerFactory
{
    protected $instances = [];
    protected $resolver;
    protected $capabilities;
    protected $loader;
    protected $serializer;
    protected $validator;
    protected $dispatcher;
    protected $http;

    public function __construct(
        ResolverInterface $resolver,
        CapabilitiesFactory $capabilities,
        LoaderFactory $loader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EventDispatcherInterface $dispatcher,
        Http $http
    ) {
        $this->resolver = $resolver;
        $this->capabilities = $capabilities;
        $this->loader = $loader;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->dispatcher = $dispatcher;
        $this->http = $http;
    }

    /**
     * Make a server object for the given host
     *
     * @param string $host
     *
     * @return Server
     */
    public function make($host)
    {
        $host = $this->resolver->resolve($host, '/');
        $hash = md5($host);

        if (isset($this->instances[$hash])) {
            return $this->instances[$hash];
        }

        $validator = new Validator(
            $this->validator,
            new ResourceNormalizer
        );
        $instance = new Server(
            $this->capabilities->make($host),
            new Client(
                $this->loader->make($host),
                $this->serializer,
                $this->resolver,
                $validator,
                $this->dispatcher,
                $this->http
            )
        );
        $this->instances[$hash] = $instance;

        return $instance;
    }
}
