<?php
namespace Victoire\Bundle\CoreBundle\Cache;

use Doctrine\Common\Cache\PhpFileCache;
use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * this class handle cache system
 **/
class VictoireCache extends PhpFileCache
{
    const EXTENSION = '.victoire.cache.php';
    protected $debug;

    /**
     * Constructor
     *
     * @param boolean      $debug       The debug environment
     */
    public function __construct($debug, $directory, $extension = self::EXTENSION)
    {
        $this->debug = $debug;
        parent::__construct($directory, $extension);
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id cache id The id of the cache entry to fetch.
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function get($id, $defaultValue = null)
    {
        if ($this->contains($id)) {
            return $this->fetch($id);
        }

        return $defaultValue;
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id   The cache id.
     * @param mixed  $data The cache entry/data.
     *
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function save($id, $data, $ttl = 20)
    {
        if ($this->debug) {
            parent::save($id, $data, $ttl);
        } else {
            parent::save($id, $data);
        }
    }

}
