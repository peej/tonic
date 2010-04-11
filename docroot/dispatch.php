<?php

// load Tonic library
require_once '../lib/tonic.php';

// load examples
require_once '../examples/examples.php';

// handle request
$request = new Request();
$resource = $request->loadResource();
$response = $resource->exec($request);
$response->output();

?>
