<?php

$steps->When('/^execute the request$/', function($world) {
    $world->response = $world->resource->exec($world->request);
});

$steps->Then('/^the response code should be "([^"]*)"$/', function($world, $arg1) {
    if ($world->response->code != $arg1) throw new Exception;
});

$steps->Then('/^the response body should be \'([^\']*)\'$/', function($world, $arg1) {
    if ($world->response->body != $arg1) throw new Exception;
});

