<?php

// load Tonic
require_once '../src/Tonic/Autoloader.php';

// example resource
#require_once '../myresource.php';

$config = array(
    'resources' => array(
/*
        'MyResource' => array(
            'class' => 'MyResource',
            'uri' => '|^/hello/(.+)$|',
            'filename' => 'myresource.php'
        )
*/
    ),
    'cache' => new Tonic\MetadataCache('/tmp/tonic.cache')
);

$request = new Tonic\Request($config);

#$request->mount('myNamespace', '/woot');

#echo $request;

$resource = $request->loadResource();
$response = $resource->exec();

$response->output();
