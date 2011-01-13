<?php

/**
 * Interface providing basic methods for a caching system.
 * @author Adam Cooper <adam@networkpie.co.uk>
 *
 */
interface Tonic_Cache_Type {
	
	/**
	 * Requests an item from the cache using the specified key
	 * @param string $key A unique identifier for the cached data	 * 
	 * @param mixed $config A named array containing configuration options specific to your cache choice
	 * @return mixed The cached object
	 */
	public function get($key, $config = array());
	
	/**
	 * Caches an item indexed using the specified key
	 * @param string $key A unique identifier to store the cached object under
	 * @param mixed $data
	 * @param mixed $config A named array containing configuration options specific to your cache choice
	 * @return boolean True if the cache save was successful, false otherwise.
	 */
	public function set($key, $data, $config = array());
	
}