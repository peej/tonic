<?php

// load Tonic
require_once '../src/Tonic/Autoloader.php';

$config = array(
    'load' => array('../src/Tyrell/*.php'), // load example resources
    #'cache' => new Tonic\MetadataCache('/tmp/tonic.cache') // use the metadata cache
);

$request = new Tonic\Request($config);

#$request->mount('myNamespace', '/woot');

#echo $request;

$resource = $request->loadResource();
$response = $resource->exec();

$response->output();
