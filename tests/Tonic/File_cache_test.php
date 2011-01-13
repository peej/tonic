<?php

require_once ('../lib/Tonic/Cache/Type.php');
require_once ('../lib/Tonic/Cache/FileCache.php');
require_once ('../lib/Tonic/Cache/Factory.php');

/**
 * Tests for file caching system.
 * @author Adam Cooper <adam@networkpie.co.uk>
 *
 */
class FileCacheTester extends UnitTestCase {
	
	private $fileCacheName = 'Tonic_Cache_FileCache';
	private $testItemName = 'test.item';
	private $testItemPath;
    
    function __construct() {
        $this->testItemPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->testItemName;
    }	
	
	function tearDown() {
        @unlink($this->testItemPath);
    }
	
	function testLoadAFileCache() {
		
		$fileCache = Tonic_Cache_Factory::getCache($this->fileCacheName);
				
		$this->assertIsA($fileCache, $this->fileCacheName);
		
	}
	
	function testCacheAnItemWithFileCache() {
		
		$fileCache = Tonic_Cache_Factory::getCache($this->fileCacheName);
		
		$testItem = array('test');
	
		$this->assertTrue($fileCache->set($this->testItemName, $testItem));
		$this->assertTrue(file_exists($this->testItemPath));
		
	}
	
	function testGetAnItemFromFileCache() {
		
		$fileCache = Tonic_Cache_Factory::getCache($this->fileCacheName);
		
		$testItem = array('test');
		$fileCache->set($this->testItemName, $testItem);
		
		$readItem = $fileCache->get($this->testItemName);
		$this->assertEqual($readItem, $testItem);		
	}
	
	function testOverwriteTheCacheItemInFileCache() {
		
		$fileCache = Tonic_Cache_Factory::getCache($this->fileCacheName);
		
		$testItem = array('test');
		$fileCache->set($this->testItemName, $testItem);
		
		$testItem = array('test.two');
		$fileCache->set($this->testItemName, $testItem);
		
		$readItem = $fileCache->get($this->testItemName);
		$this->assertEqual($readItem, $testItem);
		
	}
	
}