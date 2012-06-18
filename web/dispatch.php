<?php

require_once '../src/Tonic/Autoloader.php';

require_once '../myresource.php';

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
    #'cache' => new Tonic\MetadataCache('/tmp/tonic.cache')
);

$request = new Tonic\Request($config);

#$request->mount('myNamespace', '');

$resource = $request->loadResource();
$response = $resource->exec();

$response->output();
