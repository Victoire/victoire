<?php
namespace Victoire\Bundle\CoreBundle\Cache;

use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * this class handle cache system
 **/
class ApcCache
{
    protected $cache;
    protected $debug;

    /**
     * Constructor
     *
     * @param unknown     $cache
     * @param boolean     $debug       The debug environment
     * @param FileLocator $fileLocator
     */
    public function __construct($cache, $debug, FileLocator $fileLocator)
    {
        $this->cache = new $cache();
        $this->debug = $debug;
        $this->uniqId = md5($fileLocator->locate('@VictoireCoreBundle/Cache/ApcCache.php'));
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id cache id The id of the cache entry to fetch.
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function fetch($id, $defaultValue = null)
    {
        $id = $this->uniqId . '-' . $id;
        if ($this->contains($id)) {
            return $this->cache->fetch($id);
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
    public function save($id, $data)
    {
        $id = $this->uniqId . '-' . $id;

        if (!$this->apcIsEnabled()) {
            return false;
        }

        if ($this->debug) {
            $this->cache->save($id, $data, 20);
        } else {
            $this->cache->save($id, $data);
        }

        return false;
    }

    /**
     * Does the cache contains this key
     *
     * @param unknown $key
     *
     * @return boolean
     */
    protected function contains($key)
    {
        return $this->apcIsEnabled() && $this->cache->contains($key);
    }

    /**
     * Is apc enabled
     *
     * @return boolean
     */
    protected function apcIsEnabled()
    {
        //consider cache enabled only if apc is loaded and enabled and debug is false
        return extension_loaded('apc') && ini_get('apc.enabled');
    }

}
