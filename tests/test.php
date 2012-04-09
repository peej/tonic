<?php

error_reporting(error_reporting() & ~2048 & ~8192); // Make sure E_STRICT and E_DEPRECATED are disabled

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');

$core = new TestSuite('Core');

require_once('../lib/tonic.php');

$core->addFile('request.php');
$core->addFile('resource.php');
$core->addFile('response.php');
$core->addFile('filesystem.php');
$core->addFile('filesystemcollection.php');

$test = new TestSuite('Tonic');
$test->add($core);

//*
@include_once 'PHP/CodeCoverage.php';
if (class_exists('PHP_CodeCoverage')) {
    $coverage = new PHP_CodeCoverage;
    $coverage->start('Tonic');
}
//*/

if (TextReporter::inCli()) {
	$test->run(new TextReporter());
} else {
    $test->run(new HtmlReporter());
}

if (isset($coverage)) {
    $coverage->stop();
    
    require_once 'PHP/CodeCoverage/Report/HTML.php';
    
    $writer = new PHP_CodeCoverage_Report_HTML;
    $writer->process($coverage, 'report');
}

