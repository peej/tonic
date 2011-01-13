<?php

/**
 * Implements a cache using a filesystem file.
 * @author Adam Cooper <adam@networkpie.co.uk>
 *
 */
class Tonic_Cache_FileCache implements Tonic_Cache_Type {
	
	const DEFAULT_CACHE_TTL = 60;
	
	public function get($key, $config = array()) {
		
		if (!isset($config['ttl']))
			$config['ttl'] = self::DEFAULT_CACHE_TTL;
		
		$filemtime = filemtime(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $key);
		if (!$filemtime || (time() - $filemtime >= $config['ttl']))
			return false;
		
		if (!$data = file_get_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $key))
			return false;
			
		return unserialize($data);
		
	}
	
	public function set($key, $data, $config = array()) {
		
		if (!file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $key, serialize($data), LOCK_EX))
			return false;
		
		return true;
		
	}
}