<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{

    var $path;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
		$this->config = array();
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tonictest'.DIRECTORY_SEPARATOR;
    }

    /**
     * @xBeforeSuite
     */
    public static function setupCoverage($event)
    {
        global $coverage;
        require 'PHP/CodeCoverage/Autoload.php';
        if (class_exists('PHP_CodeCoverage')) {
            $coverage = new PHP_CodeCoverage;
            $coverage->start('Tonic');
        }
    }

    /**
     * @xAfterSuite
     */
    public static function finishUpCoverage($event)
    {
        global $coverage;
        if (isset($coverage)) {
            $coverage->stop();
            //$writer = new PHP_CodeCoverage_Report_Clover;
            //$writer->process($coverage, 'clover.xml');
            $writer = new PHP_CodeCoverage_Report_HTML;
            $writer->process($coverage, 'report');
        }
    }

    /**
     * @Given /^the request URI of "([^"]*)"$/
     */
    public function theRequestUriOf($uri)
    {
		$this->config['uri'] = $uri;
    }

    /**
     * @When /^I create a request object$/
     */
    public function iCreateARequestObject()
    {
		$this->request = new Request($this->config);
    }

    /**
     * @Then /^I should see a request URI of "([^"]*)"$/
     */
    public function iShouldSeeARequestUriOf($uri)
    {
		if ($this->request->uri != $uri)
			throw new Exception;
    }

    /**
     * @Given /^the request method of "([^"]*)"$/
     */
    public function theRequestMethodOf($method)
    {
		$this->config['method'] = $method;
    }

    /**
     * @Then /^I should see a request method of "([^"]*)"$/
     */
    public function iShouldSeeARequestMethodOf($method)
    {
		if ($this->request->method != $method)
			throw new Exception;
    }

    /**
     * @Given /^the request data of "([^"]*)"$/
     */
    public function theRequestDataOf($data)
    {
		$this->config['data'] = $data;
    }

    /**
     * @Given /^I should see the request data "([^"]*)"$/
     */
    public function iShouldSeeTheRequestData($data)
    {
		if($this->request->data != $data)
			throw new Exception;
    }

    /**
     * @Then /^I should see a negotiated URI of "([^"]*)"$/
     */
    public function iShouldSeeANegotiatedUriOf($uri)
    {
		if($this->request->negotiatedUris != explode(',', $uri))
			throw new Exception;
    }

    /**
     * @Then /^I should see a format negotiated URI of "([^"]*)"$/
     */
    public function iShouldSeeAFormatNegotiatedUriOf($uri)
    {
		if($this->request->formatNegotiatedUris != explode(',', $uri))
			throw new Exception;
    }

    /**
     * @Then /^I should see a language negotiated URI of "([^"]*)"$/
     */
    public function iShouldSeeALanguageNegotiatedUriOf($uri)
    {
		if($this->request->languageNegotiatedUris != explode(',', $uri))
			throw new Exception;
    }

    /**
     * @Given /^the accept header of "([^"]*)"$/
     */
    public function theAcceptHeaderOf($header)
    {
		$this->config['accept'] = $header;
    }

    /**
     * @Given /^the language accept header of "([^"]*)"$/
     */
    public function theLanguageAcceptHeaderOf($header)
    {
		$this->config['acceptLang'] = $header;
    }

    /**
     * @Given /^an if match header of '([^']*)'$/
     */
    public function anIfMatchHeaderOf($header)
    {
		$this->config['ifMatch'] = $header;
    }

    /**
     * @Then /^I should see an if match header of "([^"]*)"$/
     */
    public function iShouldSeeAnIfMatchHeaderOf($header)
    {
		if($this->request->ifMatch != explode(',', $header))
			throw new Exception;
    }

    /**
     * @Then /^if match should match "([^"]*)"$/
     */
    public function ifMatchShouldMatch($match)
    {
		if(!$this->request->ifMatch($match))
			throw new Exception;
    }

    /**
     * @Given /^an if none match header of '([^']*)'$/
     */
    public function anIfNoneMatchHeaderOf($header)
    {
		$this->config['ifNoneMatch'] = $header;
    }

    /**
     * @Then /^I should see an if none match header of "([^"]*)"$/
     */
    public function iShouldSeeAnIfNoneMatchHeaderOf($header)
    {
		if($this->request->ifNoneMatch != explode(',', $header))
			throw new Exception;
    }

    /**
     * @Then /^if none match should match "([^"]*)"$/
     */
    public function ifNoneMatchShouldMatch($match)
    {
		if(!$this->request->ifNoneMatch($match))
			throw new Exception;
    }

    /**
     * @Then /^if none match should not match "([^"]*)"$/
     */
    public function ifNoneMatchShouldNotMatch($match)
    {
		if($this->request->ifNoneMatch($match))
			throw new Exception;
    }

    /**
     * @Given /^I load the resource$/
     */
    public function iLoadTheResource()
    {
		$this->resource = $this->request->loadResource();
    }

    /**
     * @Then /^I should fail to load the resource$/
     */
    public function iShouldFailToLoadTheResource()
    {
		try {
			$this->request->loadResource();
		} catch(ResponseException $e) {
			if($e->getCode() != Response::NOTFOUND)
				throw new Exception;
		}
    }

    /**
     * @Then /^I should have a response of type "([^"]*)"$/
     */
    public function iShouldHaveAResponseOfType($type)
    {
		if(get_class($this->resource) != $type)
			throw new Exception;
    }

    /**
     * @Then /^I should see resource "([^"]*)" metadata of "([^"]*)"$/
     */
    public function iShouldSeeResourceMetadataOf($argument1, $argument2)
    {
		if($this->request->resources[$this->request->uri][$argument1] != $argument2)
			throw new Exception("metadata $argument1: want $argument2, got {$this->request->resources[$this->request->uri][$argument1]}");
    }

    /**
     * @Given /^a mounting of "([^"]*)" to "([^"]*)"$/
     */
    public function aMountingOfTo($argument1, $argument2)
    {
		$this->config['mount'][$argument1] = $argument2;
    }

    /**
     * @Given /^execute the request$/
     */
    public function executeTheRequest()
    {
    	$this->response = $this->resource->exec($this->request);
    }

    /**
     * @Given /^an accept encoding of "([^"]*)"$/
     */
    public function anAcceptEncodingOf($encoding)
    {
		$this->config['acceptEncoding'] = $encoding;
    }

#    /**
#     * @Given /^I process content encoding$/
#     */
#    public function iProcessContentEncoding()
#    {
#		$this->response->doContentEncoding();
#    }

    /**
     * @Then /^the response header "([^"]*)" should contain '([^']*)'$/
     */
    public function theResponseHeaderShouldContain($header, $contents)
    {
		if($this->response->headers[$header] != $contents)
			throw new Exception;
    }

#    /**
#     * @Then /^the response body should be ([^ ]*) and be "([^"]*)"$/
#     */
#    public function theResponseBodyShouldBeTransformedAndBe($transform, $contents)
#    {
#    	switch ($transform) {
#    	case 'gzipped':
##    	    var_dump($this->response->body, $contents, gzencode($contents));
#    	    if ($this->response->body != gzencode($contents)) throw new Exception;
#    	    break;
#    	case 'deflated':
#    	    if ($this->response->body != gzdeflate($contents)) throw new Exception;
#    	    break;
#    	case 'compressed':
#    	    if ($this->response->body != gzcompress($contents)) throw new Exception;
#    	    break;
#    	}
#    }

    /**
     * @Given /^I add a cache header of "([^"]*)"$/
     */
    public function iAddACacheHeaderOf($header)
	{
		if($header == '') {
			$this->response->addCacheHeader();
		} else {
			$this->response->addCacheHeader($header);
		}
	}

    /**
     * @Given /^I add an etag header of "([^"]*)"$/
     */
    public function iAddAnEtagHeaderOf($etag)
    {
		$this->response->addEtag($etag);
    }

    /**
     * @Then /^I should fail to load the resource with response code "([^"]*)" and body '([^']*)'$/
     */
    public function iShouldFailToLoadTheResourceWithResponseCodeAndBody($code, $body)
    {
		try {
			$this->request->loadResource();
		} catch(Exception $e) {
			if($e->getCode() != $code)
				throw new Exception('bad code ' . $e->getCode() . " (want $code)");
			if($e->getMessage() != $body)
				throw new Exception('bad error message ' . $e->getMessage() . " (want $body)");
		}
    }

    /**
     * @Then /^the response code should be "([^"]*)"$/
     */
    public function theResponseCodeShouldBe($code)
    {
		if($this->response->code != $code)
			throw new Exception;
    }

    /**
     * @Given /^the response body should be '([^']*)'$/
     */
    public function theResponseBodyShouldBe($body)
    {
		if($this->response->body != $body)
			throw new Exception;
    }

    /**
     * @Given /^the filesystem test data is setup$/
     */
    public function theFilesystemTestDataIsSetup()
    {
        if (!is_dir($this->path))
            mkdir($this->path, 0777, TRUE);
        file_put_contents($this->path.'tonicFilesystemTest', 'test');
        file_put_contents($this->path.'default.html', 'test');
    }

    /**
     * @Given /^the written file "([^"]*)" should contain \'([^\']*)\'$/
     */
    public function theWrittenFileShouldContain($filename, $contents)
    {
        if (file_get_contents($this->path.$filename) != $contents)
            throw new Exception;
    }

    /**
     * @Given /^the written file "([^"]*)" should not exist$/
     */
    public function theWrittenFileShouldNotExist($filename)
    {
        if (file_exists($this->path.$filename))
            throw new Exception;
    }

}
