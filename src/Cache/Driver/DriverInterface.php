<?php

namespace WordPress\Cache\Driver;

use WordPress\Cache\Cache;

interface DriverInterface 
{
	
	/**
	 * Returns the key prefix
	 * 
	 * @return string Cache key prefix
	 */	
	public function getPrefix();
	
	/**
	 * Whether the given item exists in the cache.
	 * 
	 * @param string $key Item key.
	 * @return boolean True if a cached value exists, otherwise false.
	 */
	public function exists($key);
	
	/**
	 * Returns a value from the cache.
	 * 
	 * @param string $key Item key.
	 * @return mixed Cached value, if it exists, otherwise null.
	 */
	public function get($key);

	/**
	 * Sets a value in the cache.
	 * 
	 * @param string $key Item key
	 * @param scalar $value Item value
	 * @param int $ttl [Optional] Lifetime, in seconds, for the item. Defaults to "Cache::DEFAULT_TTL"
	 */
	public function set($key, $value, $ttl = Cache::DEFAULT_TTL);

	/**
	 * Removes an item from the cache.
	 * 
	 * @param string $key Item key
	 */
	public function delete($key);

	/**
	 * Increments an item value by the value given.
	 * 
	 * @param string $key Item key
	 * @param int $value [Optional] Value by which to increment the value. Default 1
	 * @param int $ttl [Optional] Lifetime, in seconds, for the item. Defaults to "Cache::DEFAULT_TTL"
	 */
	public function incr($key, $value = 1, $ttl = Cache::DEFAULT_TTL);

	/**
	 * Decrements an item value by the value given.
	 * 
	 * @param string $key Item key
	 * @param int $value [Optional] Value by which to decrement the value. Default 1
	 * @param int $ttl [Optional] Lifetime, in seconds, for the item. Defaults to "Cache::DEFAULT_TTL"
	 */
	public function decr($key, $value = 1, $ttl = Cache::DEFAULT_TTL);

	/**
	 * Flushes all items from the cache.
	 */
	public function flush();

}
