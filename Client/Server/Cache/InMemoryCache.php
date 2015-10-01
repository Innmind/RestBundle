<?php

namespace Innmind\RestBundle\Client\Server\Cache;

use Innmind\RestBundle\Client\Server\CacheInterface;

class InMemoryCache implements CacheInterface
{
    protected $data = [];
    protected $isFresh = false;

    /**
     * {@inheritdoc}
     */
    public function save($name, $url)
    {
        $this->data[(string) $name] = (string) $url;
        $this->isFresh = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_key_exists((string) $name, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf(
                'No resource named "%s" found',
                $name
            ));
        }

        return $this->data[(string) $name];
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($this->data[(string) $name]);
            $this->isFresh = true;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh()
    {
        return $this->isFresh;
    }
}
