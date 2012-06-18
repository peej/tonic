<?php

require_once 'lib/Tonic/Autoloader.php';
Tonic\Autoloader::register();

#require_once 'myresource.php';

$request = new Tonic\Request();

$request->mount('', 'myresource.php');

$resource = $request->loadResource();
$response = $resource->exec();

$response->output();
