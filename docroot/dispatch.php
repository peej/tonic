<?php

// load Tonic library
require_once '../lib/Tonic/Resource.php';
require_once '../lib/Tonic/NoResource.php';
require_once '../lib/Tonic/Response.php';
require_once '../lib/Tonic/Request.php';

// load Tonic Caching
require_once '../lib/Tonic/Cache/Factory.php';
require_once '../lib/Tonic/Cache/Type.php';
require_once '../lib/Tonic/Cache/FileCache.php';

// load examples
require_once '../examples/examples.php';

// handle request
$request = new Tonic_Request(array('cache'=>'Tonic_Cache_FileCache', 'cache.options'=>array('ttl'=>'60')));
$resource = $request->loadResource();
$response = $resource->exec($request);
$response->output();

?>
