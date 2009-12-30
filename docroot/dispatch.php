<?php

require_once '../lib/tonic.php';

$request = new Request();
$resource = $request->loadResource();
$response = $resource->exec();
$response->output();

?>
