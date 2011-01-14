<?php

namespace Tonic\Cache;

class Factory {
	
	/**
	 * Returns a cache object ready to go.
	 * @param string $type
	 * @return Tonic\Cache\Type
	 */
	public static function getCache($type) {
		
		if (class_exists($type))
			return new $type;
		
		throw new \Exception('Caching class ' . $type . 
				' does not exist. Please ensure you have entered the ' . 
				'classname correctly and that it has been/can be loaded.');
		
	}
	
}