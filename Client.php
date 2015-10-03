<?php

namespace Innmind\RestBundle;

use Innmind\RestBundle\Client\ServerFactory;

class Client
{
    protected $server;

    public function __construct(ServerFactory $server)
    {
        $this->server = $server;
    }

    /**
     * Return a client for the given server
     *
     * @param string $host
     *
     * @return Server
     */
    public function server($host)
    {
        return $this->server->make($host);
    }
}
