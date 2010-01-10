<?php

require_once '../lib/tonic.php';
require_once '../resources/example.php';
require_once '../resources/filesystem.php';
require_once '../resources/filesystemcollection.php';

$request = new Request();
$resource = $request->loadResource();
$response = $resource->exec($request);
$response->output();

?>
