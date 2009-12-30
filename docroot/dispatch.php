<?php

require_once '../lib/tonic.php';
require_once '../resources/default.php';

$request = new Request();
$resource = $request->loadResource();
$response = $resource->exec($request);
$response->output();

?>
