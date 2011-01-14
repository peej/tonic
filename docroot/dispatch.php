<?php

// load Tonic library
require_once '../lib/Tonic/Resource.php';
require_once '../lib/Tonic/NoResource.php';
require_once '../lib/Tonic/Response.php';
require_once '../lib/Tonic/Request.php';

// load Tonic Caching
require_once '../lib/Tonic/Cache/Factory.php';
require_once '../lib/Tonic/Cache/Type.php';
require_once '../lib/Tonic/Cache/FileCache.php';

// load examples
require_once '../examples/examples.php';

// handle request
$request = new Tonic\Request(array(
		'cache'=>array(
				'type'=>'Tonic\Cache\FileCache', 
				'options'=>array(
						'ttl'=>'60',
						'cachepath'=>sys_get_temp_dir() . DIRECTORY_SEPARATOR,
						'prefix'=>'tonic.'
				)
		)
));
$resource = $request->loadResource();
$response = $resource->exec($request);
$response->output();

?>
