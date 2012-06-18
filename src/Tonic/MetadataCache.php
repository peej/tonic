<?php

namespace Tonic;

/**
 * Cache resource metadata between invocations
 */
class MetadataCache {

    private $filename;
    
    function __construct($filename) {
        $this->filename = $filename;
    }

    public function isCached() {
        return is_readable($this->filename);
    }

    public function load() {
        return unserialize(file_get_contents($this->filename));
    }

    public function save($resources) {
        file_put_contents($this->filename, serialize($resources));
    }

}