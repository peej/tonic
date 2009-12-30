<?php

require_once '../lib/tonic.php';
require_once '../resources/default.php';

$request = new Request();
$resource = $request->loadResource();
var_dump($resource);
$response = $resource->exec();
$response->output();

?>
