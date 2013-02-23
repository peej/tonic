<?php

// load autoloader (delete as appropriate)
if (@include('../src/Tonic/Autoloader.php')) { // use Tonic autoloader
    #new Tonic\Autoloader('myNamespace'); // add another namespace
} elseif (!@include('../vendor/autoload.php')) { // use Composer autoloader
    die('Could not find autoloader');
}

$config = array(
    'load' => array(
        '../src/Tyrell/*.php', // load example resources
        '../vendor/peej/tonic/src/Tyrell/*.php' // load examples from composer's vendor directory
    ),
    #'mount' => array('Tyrell' => '/nexus'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCacheFile('/tmp/tonic.cache') // use the metadata cache
    #'cache' => new Tonic\MetadataCacheAPC // use the metadata cache
);

$app = new Tonic\Application($config);

#echo $app; die;

$request = new Tonic\Request();

#echo $request; die;

try {

    $resource = $app->getResource($request);

    #echo $resource; die;

    $response = $resource->exec();

} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $e->getMessage());

} catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';

} catch (Tonic\Exception $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}

#echo $response;

$response->output();
