<?php

namespace WordPress;

use ArrayAccess;
use Countable;

interface DI extends \ArrayAccess, \Countable
{
	
	/**
	 * Returns the default instance, if it exists.
	 * 
	 * @return \WordPress\DI|null
	 */
	public static function instance();
	
	/**
	 * Sets the default instance.
	 * 
	 * @param \WordPress\DI $di
	 */
	public static function setInstance(DI $di);
	
	/**
	 * Whether a default instance exists.
	 * 
	 * @return boolean
	 */
	public static function hasInstance();
		
	/* --------------------------------------------------------
	 * Service accessors and mutators
	 * ----------------------------------------------------- */
	
	/**
	 * Adds a service to the container.
	 * 
	 * @param \WordPress\Di\Service $service
	 * 
	 * @return \WordPress\DI
	 */
	public function addService(Di\Service $service);
	
	/**
	 * Fetches a service by key.
	 * 
	 * @param string $key
	 * 
	 * @return \WordPress\Di\Service
	 */
	public function getService($key);
		
	/* --------------------------------------------------------
	 * Accessors and mutators
	 * ----------------------------------------------------- */
	
	/**
	 * Register a dependency.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param boolean $shared [Optional] Default = false
	 * 
	 * @return \WordPress\DI
	 */
	public function set($key, $value, $shared = false);
	
	/**
	 * Register a shared dependency.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * 
	 * @return \WordPress\DI
	 */
	public function setShared($key, $value);
	
	/**
	 * Resolves a dependency value.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function get($key, $arg = null);
	
	/**
	 * Checks whether an item exists with the given key.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function has($key);
	
	/**
	 * Removes an item by key.
	 * 
	 * @param string $key
	 * 
	 * @return \WordPress\DI
	 */
	public function remove($key);
	
	/**
	 * Adds a factory method to the container.
	 * 
	 * @param string $key Identifier for the factory.
	 * @param object $factory Invokable object/closure.
	 * 
	 * @return \WordPress\DI
	 */
	public function factory($key, $factory);
	
	/**
	 * Adds an alias for the given service.
	 * 
	 * The service given by $key must already exist.
	 * 
	 * @param string $key
	 * @param string $alias
	 * 
	 * @return \WordPress\DI
	 */
	public function alias($key, $alias);
	
	/**
	 * Checks whether the given key is an alias.
	 * 
	 * @param string $key
	 * 
	 * @return bool
	 */
	public function isAlias($key);
	
	/**
	 * Resolves the given alias or key to its real key.
	 * 
	 * Returns null if the key does not resolve to a real key.
	 * 
	 * @param string $key
	 * 
	 * @return string|null
	 */
	public function getRealKey($key);
	
	/* --------------------------------------------------------
	 * Magic methods
	 * ----------------------------------------------------- */
	
	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value);
	
	/**
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function __get($key);
	
	/**
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function __isset($key);
	
	/**
	 * @param string $key
	 */
	public function __unset($key);
	
}