<?php

// load Tonic library
require_once '../lib/tonic.php';

// example resource mounted at /example
require_once '../resources/example.php';

// example basic filesysystem resource collection mounted at /collection
require_once '../resources/filesystem.php';
require_once '../resources/filesystemcollection.php';

// handle request
$request = new Request();
$resource = $request->loadResource();
$response = $resource->exec($request);
$response->output();

?>
