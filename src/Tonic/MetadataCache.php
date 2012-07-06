<?php

namespace Tonic;

/**
 * Cache resource metadata between invocations
 *
 * This class serializes the resource metadata and writes it to disk for reading in a later request.
 */
class MetadataCache
{
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Is there already cache file
     * @return boolean
     */
    public function isCached()
    {
        return is_readable($this->filename);
    }

    /**
     * Load the resource metadata from disk
     * @return str[]
     */
    public function load()
    {
        return unserialize(file_get_contents($this->filename));
    }

    /**
     * Save resource metadata to disk
     * @param  str[]   $resources Resource metadata
     * @return boolean
     */
    public function save($resources)
    {
        return file_put_contents($this->filename, serialize($resources));
    }

    public function __toString()
    {
        return $this->filename;
    }

}
