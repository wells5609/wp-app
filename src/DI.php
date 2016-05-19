<?php

namespace WordPress;

use ArrayAccess;
use Countable;
use Closure;
use RuntimeException;
use InvalidArgumentException;

class DI implements ArrayAccess, Countable
{
	
	/**
	 * Default instance.
	 * 
	 * @var \WordPress\DI
	 */
	protected static $instance;
	
	/**
	 * Services
	 * 
	 * @var \WordPress\Di\Service[]
	 */
	protected $services = array();
	
	/**
	 * Service aliases.
	 * 
	 * @var string[]
	 */
	protected $aliases = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		if (! isset(static::$instance)) {
			static::$instance = $this;
		}
	}
	
	/**
	 * Returns the default instance, if it exists.
	 * 
	 * @return \WordPress\DI|null
	 */
	public static function instance() {
		return static::$instance;
	}
	
	/**
	 * Sets the default instance.
	 * 
	 * @param \WordPress\DI $di
	 */
	public static function setInstance(DI $di) {
		static::$instance = $di;
	}
	
	/**
	 * Whether a default instance exists.
	 * 
	 * @return boolean
	 */
	public static function hasInstance() {
		return isset(static::$instance);
	}
		
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
	public function addService(Di\Service $service) {
		$this->services[$service->getName()] = $service;
		return $this;
	}
	
	/**
	 * Fetches a service by key.
	 * 
	 * @param string $key
	 * 
	 * @return \WordPress\Di\Service
	 */
	public function getService($key) {
		if ($key = $this->getRealKey($key)) {
			return $this->services[$key];
		}
		return null;
	}
		
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
	public function set($key, $value, $shared = false) {
		if ($shared) {
			$this->setShared($key, $value);
		} else {
			$this->factory($key, $value);
		}
		return $this;
	}
	
	/**
	 * Register a shared dependency.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * 
	 * @return \WordPress\DI
	 */
	public function setShared($key, $value) {
		
		$service = new Di\Service($this, $key);
		
		if ($value instanceof Di\DiAwareInterface) {
			$value->setDI($this);
		}
		
		$service->share($value);
		
		$this->services[$key] = $service;
		
		return $this;
	}
	
	/**
	 * Resolves a dependency value.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function get($key, $arg = null) {
		
		$service = $this->getService($key);
		
		if (null === $service) {
			return null;
		}
		
		if (null === $arg) {
			return $service->resolve();
		}
		
		$args = func_get_args();
		array_shift($args);
		
		return $service->resolve($args);
	}
	
	/**
	 * Checks whether an item exists with the given key.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function has($key) {
		$key = $this->getRealKey($key);
		return null !== $key;
	}
	
	/**
	 * Removes an item by key.
	 * 
	 * @param string $key
	 * 
	 * @return \WordPress\DI
	 */
	public function remove($key) {
		if ($key = $this->getRealKey($key)) {
			unset($this->services[$key]);
		}
		return $this;
	}
	
	/**
	 * Adds a factory method to the container.
	 * 
	 * @param string $key Identifier for the factory.
	 * @param object $factory Invokable object/closure.
	 * 
	 * @return \WordPress\DI
	 */
	public function factory($key, $factory) {
		
		$service = new Di\Service($this, $key);
		
		if ($factory instanceof Di\DiAwareInterface) {
			$factory->setDI($this);
		}
		
		$service->factory($factory);
		
		$this->services[$key] = $factory;
		
		return $this;
	}
	
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
	public function alias($key, $alias) {
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
	 * Resolves the given alias or key to its real key.
	 * 
	 * Returns null if the key does not resolve to a real key.
	 * 
	 * @param string $key
	 * 
	 * @return string|null
	 */
	public function getRealKey($key) {
		if (isset($this->services[$key])) {
			return $key;
		}
		if (isset($this->aliases[$key])) {
			return $this->getRealKey($this->aliases[$key]);
		}
		return null;
	}
	
	/* --------------------------------------------------------
	 * Implements Countable
	 * ----------------------------------------------------- */
	
	/**
	 * Returns the number of services.
	 * 
	 * @return int
	 */
	public function count() {
		return count($this->services);
	}
	
	/* --------------------------------------------------------
	 * Implements ArrayAccess
	 * ----------------------------------------------------- */
	
	/**
	 * Register a shared service.
	 * 
	 * @param string $key Dependency key.
	 * @param mixed $value
	 */
	public function offsetSet($key, $value) {
		$this->setShared($key, $value);
	}
	
	/**
	 * Resolves a service value.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function offsetGet($key) {
		return $this->get($key);
	}
	
	/**
	 * Checks whether a service with the given key exists.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function offsetExists($key) {
		return $this->has($key);
	}
	
	/**
	 * Removes the service given by $key.
	 * 
	 * @param string $key
	 */
	public function offsetUnset($key) {
		$this->remove($key);
	}
	
	/* --------------------------------------------------------
	 * Magic methods
	 * ----------------------------------------------------- */
	
	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value) {
		$this->setShared($key, $value);
	}
	
	/**
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function __get($key) {
		return $this->get($key);
	}
	
	/**
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function __isset($key) {
		return $this->has($key);
	}
	
	/**
	 * @param string $key
	 */
	public function __unset($key) {
		$this->remove($key);
	}
	
}