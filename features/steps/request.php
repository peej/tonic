<?php

$steps->Given('/^the request URI of "([^"]*)"$/', function($world, $arg1) {
    $world->config['uri'] = $arg1;
});

$steps->Given('/^the request method of "([^"]*)"$/', function($world, $arg1) {
    $world->config['method'] = $arg1;
});

$steps->Given('/^the request data of "([^"]*)"$/', function($world, $arg1) {
    $world->config['data'] = $arg1;
});

$steps->Given('/^the accept header of "([^"]*)"$/', function($world, $arg1) {
    $world->config['accept'] = $arg1;
});

$steps->Given('/^the language accept header of "([^"]*)"$/', function($world, $arg1) {
    $world->config['acceptLang'] = $arg1;
});

$steps->Given('/^an if match header of \'([^\']*)\'$/', function($world, $arg1) {
    $world->config['ifMatch'] = $arg1;
});

$steps->Given('/^an if none match header of \'([^\']*)\'$/', function($world, $arg1) {
    $world->config['ifNoneMatch'] = $arg1;
});

$steps->Given('/^a 404 resource classname of "([^"]*)"$/', function($world, $arg1) {
    $world->config['404'] = $arg1;
});

$steps->Given('/^a mounting of "([^"]*)" to "([^"]*)"$/', function($world, $arg1, $arg2) {
    $world->config['mount'][$arg1] = $arg2;
});

$steps->Given('/^the querystring is "([^"]*)"$/', function($world, $arg1) {
    $_SERVER['QUERY_STRING'] = $arg1;
});

$steps->When('/^I create a request object$/', function($world) {
    $world->request = new Request($world->config);
});

$steps->When('/^I load the resource$/', function($world) {
    $world->resource = $world->request->loadResource();
});

$steps->Then('/^I should see a request URI of "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->uri != $arg1) throw new Exception;
});

$steps->Then('/^I should see a request method of "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->method != $arg1) throw new Exception;
});

$steps->Then('/^I should see the request data "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->data != $arg1) throw new Exception;
});

$steps->Then('/^I should see a negotiated URI of "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->negotiatedUris != explode(',', $arg1)) throw new Exception;
});

$steps->Then('/^I should see a format negotiated URI of "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->formatNegotiatedUris != explode(',', $arg1)) throw new Exception;
});

$steps->Then('/^I should see a language negotiated URI of "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->languageNegotiatedUris != explode(',', $arg1)) throw new Exception;
});

$steps->Then('/^I should have a response of type "([^"]*)"$/', function($world, $arg1) {
    if (get_class($world->resource) != $arg1) throw new Exception;
});

$steps->Then('/^I should see an if match header of "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->ifMatch != explode(',', $arg1)) throw new Exception;
});

$steps->Then('/^if match should match "([^"]*)"$/', function($world, $arg1) {
    if (!$world->request->ifMatch($arg1)) throw new Exception;
});

$steps->Then('/^I should see an if none match header of "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->ifNoneMatch != explode(',', $arg1)) throw new Exception;
});

$steps->Then('/^if none match should match "([^"]*)"$/', function($world, $arg1) {
    if (!$world->request->ifNoneMatch($arg1)) throw new Exception;
});

$steps->Then('/^if none match should not match "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->ifNoneMatch($arg1)) throw new Exception;
});

$steps->Then('/^I should see resource "([^"]*)" metadata of "([^"]*)"$/', function($world, $arg1, $arg2) {
    if ($world->request->resources[$world->request->uri][$arg1] != $arg2) throw new Exception;
});

$steps->Then('/^I should see a querystring of "([^"]*)"$/', function($world, $arg1) {
    if ($world->request->data != '?'.$arg1) throw new Exception("$arg1 does not equal {$world->request->data}");
});

