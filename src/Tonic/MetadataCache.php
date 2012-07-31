<?php

namespace Tonic;

/**
 * Cache resource metadata between invocations
 */
interface MetadataCache
{
    /**
     * Is there already cache file
     * @return boolean
     */
    public function isCached();

    /**
     * Load the resource metadata from disk
     * @return str[]
     */
    public function load();

    /**
     * Save resource metadata to disk
     * @param  str[]   $resources Resource metadata
     * @return boolean
     */
    public function save($resources);

    /**
     * Clear the cache
     */
    public function clear();

    public function __toString();

}
