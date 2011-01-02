<?php

error_reporting(error_reporting() & ~2048 & ~8192); // Make sure E_STRICT and E_DEPRECATED are disabled

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');

$core = new GroupTest('Core');

$core->addTestFile('request.php');
$core->addTestFile('resource.php');
$core->addTestFile('response.php');
$core->addTestFile('filesystem.php');
$core->addTestFile('filesystemcollection.php');

$test = new GroupTest('Tonic');
$test->addTestCase($core);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

?>
