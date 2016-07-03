<?php

namespace WordPress\DependencyInjection;

use ArrayAccess;
use Countable;
use Closure;

/**
 * Dependency injection container.
 */
class Container implements ArrayAccess, Countable
{
	
	/**
	 * Array of services.
	 * 
	 * @var \WordPress\DependencyInjection\Service[]
	 */
	protected $services = array();
	
	/**
	 * Service aliases.
	 * 
	 * @var string[]
	 */
	protected $aliases = array();
	
	/** -------------------------------------------------------
	 *  Service accessors and mutators
	 * ----------------------------------------------------- */
	
	/**
	 * Adds a service to the DI.
	 * 
	 * @param \WordPress\DependencyInjection\Service $service
	 */
	public function addService(Service $service) {
		$this->services[$service->getName()] = $service;
	}
	
	/**
	 * Returns a raw DI service, if it exists.
	 * 
	 * @param string $name
	 * 
	 * @return \WordPress\DependencyInjection\Service
	 */
	public function getService($name) {
		return isset($this->services[$name]) ? $this->services[$name] : null;
	}
		
	/** -------------------------------------------------------
	 *  Accessors and mutators
	 * ----------------------------------------------------- */
	
	/**
	 * Register a dependency.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param boolean $shared [Optional] Default = false
	 */
	public function set($key, $value, $shared = false) {
		if ($shared) {
			$this->setShared($key, $value);
		} else {
			$this->factory($key, $value);
		}
	}
	
	/**
	 * Register a shared dependency.
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function setShared($key, $value) {
		$key = $this->resolveKey($key);
		$service = new Service($this, $key);
		$service->share($value);
		$this->services[$key] = $service;
	}
	
	/**
	 * Adds a factory method to the container.
	 * 
	 * @param string $key Identifier for the factory.
	 * @param object $factory Invokable object/closure.
	 */
	public function factory($key, $factory) {
		$key = $this->resolveKey($key);
		$service = new Service($this, $key);
		$service->factory($factory);
		$this->services[$key] = $service;
	}
	
	/**
	 * Resolves a dependency value.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function get($key, array $args = null) {
		$key = $this->resolveKey($key);
		if (isset($this->services[$key])) {
			return $this->services[$key]->resolve($args);
		}
	}
	
	/**
	 * Checks whether an item exists with the given key.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function has($key) {
		return isset($this->services[$this->resolveKey($key)]);
	}
	
	/**
	 * Removes an item by key.
	 * 
	 * @param string $key
	 * 
	 * @return \WordPress\DependencyInjection\Service|null
	 */
	public function remove($key) {
		$key = $this->resolveKey($key);
		if (isset($this->services[$key])) {
			$service = $this->services[$key];
			unset($this->services[$key]);
			return $service;
		}
	}
	
	/** -------------------------------------------------------
	 *  Implements ArrayAccess
	 * ----------------------------------------------------- */
	
	/**
	 * Register a shared dependency.
	 * 
	 * @param string $key Dependency key.
	 * @param mixed $value
	 */
	public function offsetSet($key, $value) {
		$this->setShared($key, $value);
	}
	
	/**
	 * Resolves a dependency or value.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function offsetGet($key) {
		return $this->get($key);
	}
	
	/**
	 * Checks whether an item with the given key exists.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function offsetExists($key) {
		return $this->has($key);
	}
	
	/**
	 * Removes the item/factory/callback given by $key.
	 * 
	 * @param string $key
	 */
	public function offsetUnset($key) {
		$this->remove($key);
	}
	
	/** -------------------------------------------------------
	 *  Implements Countable
	 * ----------------------------------------------------- */
	
	/**
	 * Returns the number of keys.
	 * 
	 * @return int
	 */
	public function count() {
		return count($this->services);
	}
	
	/** -------------------------------------------------------
	 *  Magic methods
	 * ----------------------------------------------------- */

	/**
	 * Register a shared dependency.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value) {
		$this->setShared($key, $value);
	}
	
	/**
	 * Resolves a dependency or value.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function __get($key) {
		return $this->get($key);
	}
	
	/**
	 * Checks whether an item with the given key exists.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function __isset($key) {
		return $this->has($key);
	}
	
	/**
	 * Removes the item/factory/callback given by $key.
	 * 
	 * @param string $key
	 */
	public function __unset($key) {
		$this->remove($key);
	}
	
	/** -------------------------------------------------------
	 *  $GLOBALS methods
	 * ----------------------------------------------------- */
	
	/**
	 * Checks if a global variable with the given name exists.
	 * 
	 * @param string $name
	 * 
	 * @return boolean
	 */
	public function isGlobal($name) {
		return array_key_exists($name, $GLOBALS);
	}
	
	/**
	 * Returns a global variable.
	 * 
	 * @param string $name
	 * 
	 * @return mixed
	 */
	public function getGlobal($name) {
		return $GLOBALS[$name];
	}
	
	/**
	 * Sets a global variable.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function setGlobal($name, $value) {
		$GLOBALS[$name] = $value;
	}
	
	/** -------------------------------------------------------
	 *  Alias methods
	 * ----------------------------------------------------- */
	
	/**
	 * Adds an alias for the given service.
	 * 
	 * @param string $key
	 * @param string $alias
	 * 
	 * @return \WordPress\DependencyInjection\Container
	 */
	public function addAlias($key, $alias) {
		$this->aliases[$alias] = $key;
		return $this;
	}
	
	/**
	 * Checks whether the given key is an alias.
	 * 
	 * @param string $key
	 * 
	 * @return bool
	 */
	public function isAlias($key) {
		return isset($this->aliases[$key]);
	}
	
	/**
	 * If given an alias, resolves it to a key, otherwise returns the given key.
	 * 
	 * @param string $key
	 * 
	 * @return string
	 */
	public function resolveKey($key) {
		if (isset($this->aliases[$key])) {
			return $this->resolveKey($this->aliases[$key]);
		}
		return $key;
	}
	
}