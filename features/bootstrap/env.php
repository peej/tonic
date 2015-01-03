<?php

require_once __DIR__.'/../../src/Tonic/Autoloader.php';

set_include_path(get_include_path().':'.__DIR__.'/..');

class MockMetadataCache {
    var $cacheValue = NULL;
    function __construct() {}
    
    function isCached() {
        return !!$this->cacheValue;
    }

    function load() {
        return $this->cacheValue;
    }

    function save($resources) {
        $this->cacheValue = $resources;
    }

    function contains($className, $methodName) {
        return isset($this->cacheValue[$className]) && $this->cacheValue[$className]->getMethod($methodName);
    }
}
