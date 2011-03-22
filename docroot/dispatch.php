<?php

// load Tonic library
require_once '../lib/tonic.php';

// load examples
require_once '../examples/examples.php';

// handle request
$request = new Request();
$resource = $request->loadResource();
try {
    $response = $resource->exec($request);
} catch (AuthException $e) {
    $response = new Response($request);
    $response->code = Response::UNAUTHORIZED;
    $response->body = 'You must enter the username and password';
    $response->addHeader('WWW-Authenticate', 'Basic realm="Tonic"');
}
$response->output();

?>
