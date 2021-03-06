<?php

namespace Innmind\RestBundle\Client\Server\Cache;

use Symfony\Component\Filesystem\Filesystem;

class FileCache extends InMemoryCache
{
    protected $filePath;
    protected $filesystem;
    protected $isFresh = true;

    public function __construct($filePath)
    {
        $this->filePath = (string) $filePath;
        $this->filesystem = new Filesystem;

        if ($this->filesystem->exists($this->filePath)) {
            $this->data = require $this->filePath;
            $this->isFresh = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh()
    {
        return $this->isFresh;
    }

    /**
     * Dump the couples to the file
     *
     * @return void
     */
    public function __destruct()
    {
        $dump = var_export($this->data, true);
        $code = <<<PHP
<?php

return $dump;

PHP;

        $this->filesystem->dumpFile($this->filePath, $code);
    }
}
