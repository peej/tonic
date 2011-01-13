<?php

/**
 * Implements a cache using a filesystem file.
 * @author Adam Cooper <adam@networkpie.co.uk>
 *
 */
class Tonic_Cache_FileCache implements Tonic_Cache_Type {
	
	const DEFAULT_CACHE_TTL = 60;
	const DEFAULT_CACHE_PREFIX = 'tonic.';
	
	private $cachepath;
	private $config;
	
	public function __construct() {
		
		$this->cachepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
		
	}
	
	public function get($key, $config = array()) {
		
		$this->cleanupConfig($config);
		
		$filemtime = filemtime($this->config['cachepath'] . $this->config['prefix'] . $key);
		if (!$filemtime || (time() - $filemtime >= $this->config['ttl']))
			return false;
		
		if (!$data = file_get_contents($this->config['cachepath'] . $this->config['prefix'] . $key))
			return false;
			
		return unserialize($data);
		
	}
	
	public function set($key, $data, $config = array()) {
		
		$this->cleanupConfig($config);
		
		if (!file_put_contents($this->config['cachepath'] . $this->config['prefix'] . $key, serialize($data), LOCK_EX))
			return false;
		
		return true;
		
	}
	
	private function cleanupConfig($config) {
	
		if (!isset($config['ttl']))
			$config['ttl'] = self::DEFAULT_CACHE_TTL;
			
		if (!isset($config['cachepath']))
			$config['cachepath'] = $this->cachepath;
			
		if (!isset($config['prefix']))
			$config['prefix'] = self::DEFAULT_CACHE_PREFIX;
		
		$this->config = $config;
	}
}