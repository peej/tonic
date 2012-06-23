<?php

// load Tonic
require_once '../src/Tonic/Autoloader.php';

$config = array(
    'load' => array('../src/Tyrell/*.php'), // load example resources
    #'mount' => array('Tyrell' => '/nexus'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCache('/tmp/tonic.cache') // use the metadata cache
);

$request = new Tonic\Request($config);

#echo $request;

$resource = $request->loadResource();

#echo $resource;

$response = $resource->exec();

#echo $response;

$response->output();
