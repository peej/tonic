<?php

// load Tonic
require_once '../src/Tonic/Autoloader.php';

$config = array(
    'load' => array('../*.php', '../src/Tyrell/*.php'), // load example resources
    #'mount' => array('Tyrell' => '/nexus'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCache('/tmp/tonic.cache') // use the metadata cache
);

$app = new Tonic\Application($config);

#echo $app;

$request = new Tonic\Request();

#echo $request;

$resource = $app->getResource($request);

#echo $resource;

$response = $resource->exec();

#echo $response;

$response->output();
