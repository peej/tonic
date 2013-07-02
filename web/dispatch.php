<?php

// autoloader
require_once '../vendor/autoload.php';

$config = array(
    'load' => array('../src/ExampleResources/*.php') // load resources
);

$app = new Tonic\Application($config);

// set up the container
$container = new Pimple();
$container['db_config'] = array(
    'dsn' => 'mysql:host=localhost;dbname=%s',
    'username' => 'root',
    'password' => ''
);
$container['smarty'] = function ($c) {
    $smarty = new Smarty();
    $smarty->setTemplateDir('../views');
    $smarty->setCompileDir(sys_get_temp_dir());
    return $smarty;
};

$request = new Tonic\Request(array(
    'mimetypes' => array(
        'hal' => 'application/hal+json'
    )
));

try {

    $resource = $app->getResource($request);

    $resource->container = $container; // attach container to loaded resource

    $response = $resource->exec();

} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $e->getMessage());

} catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';

} catch (Tonic\Exception $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}

#$response->contentType = 'text/plain';

$response->output();
