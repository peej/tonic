<?php

namespace Tonic\Tests;

use Tonic as Tonic;
use UnitTestCase as UnitTestCase;

require_once ('../lib/Tonic/Cache/Type.php');
require_once ('../lib/Tonic/Cache/FileCache.php');
require_once ('../lib/Tonic/Cache/Factory.php');

/**
 * Tests for file caching system.
 * @author Adam Cooper <adam@networkpie.co.uk>
 *
 */
class FileCacheTester extends UnitTestCase {
	
	const FILE_CACHE_CLASSNAME = 'Tonic\Cache\FileCache';
	const CACHE_PREFIX = 'tonic.';
	const ITEM_NAME = 'test.item';
	
	private $testItemPath;
	private $testCacheConfig;
    
	function __construct() {
		$this->testItemPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::CACHE_PREFIX . self::ITEM_NAME;
		$this->testCacheConfig = array(
				'cachepath' => sys_get_temp_dir() . DIRECTORY_SEPARATOR,
				'prefix' => self::CACHE_PREFIX
		);
	}	
	
	function tearDown() {
		
		@unlink($this->testItemPath);
	}
	
	function testLoadAFileCache() {
		
		$fileCache = Tonic\Cache\Factory::getCache(self::FILE_CACHE_CLASSNAME);
				
		$this->assertIsA($fileCache, self::FILE_CACHE_CLASSNAME);
		
	}
	
	function testCacheAnItemWithFileCache() {
		
		$fileCache = Tonic\Cache\Factory::getCache(self::FILE_CACHE_CLASSNAME);
		
		$testItem = array('test');
	
		$this->assertTrue($fileCache->set(self::ITEM_NAME, $testItem, $this->testCacheConfig));
		$this->assertTrue(file_exists($this->testItemPath));
		
	}
	
	function testGetAnItemFromFileCache() {
		
		$fileCache = Tonic\Cache\Factory::getCache(self::FILE_CACHE_CLASSNAME);
		
		$testItem = array('test');
		$fileCache->set(self::ITEM_NAME, $testItem, $this->testCacheConfig);
		
		$readItem = $fileCache->get(self::ITEM_NAME);
		$this->assertEqual($readItem, $testItem);		
		
	}
	
	function testOverwriteTheCacheItemInFileCache() {
		
		$fileCache = Tonic\Cache\Factory::getCache(self::FILE_CACHE_CLASSNAME);
		
		$testItem = array('test');
		$fileCache->set(self::ITEM_NAME, $testItem, $this->testCacheConfig);
		
		$testItem = array('test.two');
		$fileCache->set(self::ITEM_NAME, $testItem, $this->testCacheConfig);
		
		$readItem = $fileCache->get(self::ITEM_NAME, $this->testCacheConfig);
		$this->assertEqual($readItem, $testItem);
		
	}
	
}