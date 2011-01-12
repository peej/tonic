<?php

error_reporting(error_reporting() & ~2048 & ~8192); // Make sure E_STRICT and E_DEPRECATED are disabled

require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

// Enter the url of the host at which tonic is located to enable web tests.
// e.g. define('EXAMPLE_BASE_URL', 'http://localhost/');
define('EXAMPLE_BASE_URL', '');

class AllFileTests extends TestSuite {
    function __construct() {
        parent::__construct('All Tonic Tests');
        $this->addFile(dirname(__FILE__).'/Tonic/core.php');
        if (EXAMPLE_BASE_URL != '') $this->addFile(dirname(__FILE__).'/Tonic/web.php');
    }
}

$all = new AllFileTests();

if (TextReporter::inCli()) {
	exit ($all->run(new TextReporter()) ? 0 : 1);
}
$all->run(new HtmlReporter());
