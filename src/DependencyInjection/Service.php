<?php

namespace WordPress\DependencyInjection;

use Closure;

/**
 * Represents a dependency injection service.
 */
class Service
{
	
	/**
	 * The DI container.
	 * 
	 * @var \WordPress\DependencyInjection\Container
	 */
	protected $di;
	
	/**
	 * The service name.
	 * 
	 * @var string
	 */
	protected $name;
	
	/**
	 * The shared value.
	 * 
	 * @var mixed
	 */
	protected $value;
	
	/**
	 * Whether the service is shared.
	 * 
	 * @var boolean
	 */
	protected $shared = false;
	
	/**
	 * Value or factory callback.
	 * 
	 * @var callable
	 */
	protected $callback;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\DependencyInjection\Container $di
	 * @param string $name
	 */
	public function __construct(Container $di, $name) {
		$this->di = $di;
		$this->name = $name;
	}
	
	/**
	 * Returns the service name.
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Checks whether the service is shared.
	 * 
	 * @return boolean
	 */
	public function isShared() {
		return $this->shared;
	}
	
	/**
	 * Sets the service's shared value or callback.
	 * 
	 * @param mixed $value
	 */
	public function share($value) {
		$this->shared = true;
		if ($value instanceof Closure) {
			$this->callback = $value;
		} else {
			if ($value instanceof DiAwareInterface) {
				$value->setDI($this->di);
			}
			$this->value = $value;
		}
	}
	
	/**
	 * Sets the service factory.
	 * 
	 * @param callable $factory
	 */
	public function factory(callable $factory) {
		if ($factory instanceof DiAwareInterface) {
			$factory->setDI($this->di);
		}
		$this->callback = $factory;
	}
	
	/**
	 * Resolves the service.
	 * 
	 * @param array $args [Optional]
	 * 
	 * @return mixed
	 */
	public function resolve(array $args = null) {
		
		if (isset($this->value)) {
			return $this->value;
		}
		
		if (! isset($this->callback)) {
			return null;
		}
		
		if ($args) {
			foreach($args as &$value) {
				if (strpos($value, '@') === 0) {
					$value = $this->di->get(ltrim($value, '@'));
				}
			}
			$value = call_user_func_array($this->callback, $args);
		} else {
			$callback = $this->callback;
			$value = $callback($this->di);
		}
		
		if ($value instanceof DiAwareInterface) {
			$value->setDI($this->di);
		}
		
		if ($this->shared) {
			$this->value = $value;
		}
		
		return $value;
	}
}
	