<?php

// load Tonic library
require_once '../lib/Tonic/Resource.php';
require_once '../lib/Tonic/NoResource.php';
require_once '../lib/Tonic/Response.php';
require_once '../lib/Tonic/Request.php';

// load examples
require_once '../examples/examples.php';

// handle request
$request = new Tonic_Request();
$resource = $request->loadResource();
$response = $resource->exec($request);
$response->output();

?>
