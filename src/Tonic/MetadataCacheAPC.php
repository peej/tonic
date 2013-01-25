<?php

namespace Tonic;

/**
 * Cache resource metadata between invocations
 *
 * This class writes the resource metadata to APC for reading in a later request.
 */
class MetadataCacheAPC implements MetadataCache
{
    private $cacheName = 'tonicCache';

    public function __construct($cacheName = null){
        if ($cacheName) {
            $this->cacheName = $cacheName;
        }
    }

    /**
     * Is there already cache file
     * @return boolean
     */
    public function isCached()
    {
        return apc_exists($this->cacheName);
    }

    /**
     * Load the resource metadata from disk
     * @return str[]
     */
    public function load()
    {
        return apc_fetch($this->cacheName);
    }

    /**
     * Save resource metadata to disk
     * @param  str[]   $resources Resource metadata
     * @return boolean
     */
    public function save($resources)
    {
        return apc_store($this->cacheName, $resources);
    }

    public function clear()
    {
        apc_delete($this->cacheName);
    }

    public function __toString()
    {
        $info = apc_cache_info('user');
        return 'Metadata for '.count($this->load()).' resources stored in APC at '.date('r', $info['cache_list'][0]['creation_time']);
    }

}
