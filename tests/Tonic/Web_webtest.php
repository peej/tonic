<?php

class TonicExampleTest extends WebTestCase 
{
	function testBasicHelloWorld()
	{
		$this->assertTrue($this->get(EXAMPLE_BASE_URL . '/helloworld'), 
				'Unable to fetch page. Ensure you have correctly set the variable EXAMPLE_BASE_URL');
		$this->assertMime(array('text/plain'));
		$this->assertResponse(array('200'));
		$this->assertHeader('Etag', '"4f4f2ec3b13594372708d54e0998a263"');
	}
	
	function testBasicHelloWorldWithEtag()
	{
		$this->addHeader('If-None-Match: "4f4f2ec3b13594372708d54e0998a263"');
		$this->get(EXAMPLE_BASE_URL . '/helloworld');
		$this->assertResponse(array('304'));
	}
	
	function testBasicHelloWorldWithParameter()
	{
		$this->assertTrue($this->get(EXAMPLE_BASE_URL . '/helloworld/5'), 
				'Unable to fetch page. Ensure you have correctly set the variable EXAMPLE_BASE_URL');
		$this->assertMime(array('text/plain'));
		$this->assertResponse(array('200'));
		$this->assertHeader('Etag', '"ce03a37e5e4909e7c9b7b9012ab1efb9"');
	}
	
	function testIncorrectBasicHelloWorldWithParameter()
	{
		$this->assertTrue($this->get(EXAMPLE_BASE_URL . '/helloworld/5/bad'), 
				'Unable to fetch page. Ensure you have correctly set the variable EXAMPLE_BASE_URL');
		$this->assertMime(array('text/html'));
		$this->assertResponse(array('404'));
	}
	
	function testBasicHelloWorldWithJsonAccept()
	{
		$this->addHeader('Accept: application/json');
		
		$this->assertTrue($this->get(EXAMPLE_BASE_URL . '/helloworld'), 
				'Unable to fetch page. Ensure you have correctly set the variable EXAMPLE_BASE_URL');
		$this->assertPattern('#\s/helloworld.json\s#'); // the example helloworld does not set mimetype of return.
	}
}