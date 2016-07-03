<?php

namespace WordPress\Cache\Driver;

use WordPress\Cache\Cache;

abstract class AbstractDriver implements DriverInterface
{
	
	/**
	 * String prepended to all keys
	 * 
	 * @var string
	 */
	protected $prefix;
	
	/**
	 * Construct the driver with an optional key prefix.
	 * 
	 * @param string $prefix [Optional] Key prefix
	 */
	public function __construct($prefix = null) {
		$this->setPrefix($prefix);
	}
	
	/**
	 * Sets the cache key prefix.
	 * 
	 * If $prefix is null, sets the prefix based on DOCUMENT_ROOT.
	 * 
	 * @param string $prefix [Optional] Key prefix
	 */
	public function setPrefix($prefix = null) {
		if (isset($prefix)) {
			$this->prefix = $prefix;
		} else if (defined('CACHE_PREFIX')) {
			$this->prefix = CACHE_PREFIX;
		} else {
			$var = isset($_SERVER['PWD']) ? $_SERVER['PWD'] : $_SERVER['DOCUMENT_ROOT'];
			$this->prefix = md5($var).'|';
		}
	}
	
	/**
	 * Returns the key prefix
	 * 
	 * @return string Cache key prefix
	 */
	public function getPrefix() {
		return $this->prefix;
	}
	
}
