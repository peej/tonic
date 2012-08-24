<?php

// load Tonic
require_once '../src/Tonic/Autoloader.php';

$config = array(
    'load' => array('../*.php', '../src/Tyrell/*.php'), // load example resources
    #'mount' => array('Tyrell' => '/nexus'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCacheFile('/tmp/tonic.cache') // use the metadata cache
    #'cache' => new Tonic\MetadataCacheAPC // use the metadata cache
);

$app = new Tonic\Application($config);

#echo $app;

$request = new Tonic\Request();

#echo $request;

try {

    $resource = $app->getResource($request);

    #echo $resource;

    $response = $resource->exec();

} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $e->getMessage());

} catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';

} catch (Tonic\Exception $e) {
    $response = new Tonic\Response(500, $e->getMessage());
}

#echo $response;

$response->output();
