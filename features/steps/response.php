<?php

$steps->Given('/^an accept encoding of "([^"]*)"$/', function($world, $arg1) {
    $world->config['acceptEncoding'] = $arg1;
});

$steps->When('/^I process content encoding$/', function($world) {
    $world->response->doContentEncoding();
});

$steps->Then('/^the response header "([^"]*)" should contain \'([^\']*)\'$/', function($world, $arg1, $arg2) {
    if ($world->response->headers[$arg1] != $arg2) throw new Exception;
});

$steps->Then('/^the response body should be ([^ ]*) and be "([^"]*)"$/', function($world, $arg1, $arg2) {
    switch ($arg1) {
    case 'gzipped':
        var_dump($world->response->body, $arg2, gzencode($arg2));
        if ($world->response->body != gzencode($arg2)) throw new Exception;
        break;
    case 'deflated':
        if ($world->response->body != gzdeflate($arg2)) throw new Exception;
        break;
    case 'compressed':
        if ($world->response->body != gzcompress($arg2)) throw new Exception;
        break;
    }
});

$steps->Then('/^I add a cache header of "([^"]*)"$/', function($world, $arg1) {
    if ($arg1 == '') {
        $world->response->addCacheHeader();
    } else {
        $world->response->addCacheHeader($arg1);
    }
});

$steps->Then('/^I add an etag header of "([^"]*)"$/', function($world, $arg1) {
    $world->response->addEtag($arg1);
});

