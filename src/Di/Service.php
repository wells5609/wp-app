<?php

namespace WordPress\Di;

use WordPress\DI;
use Closure;
use RuntimeException;
use InvalidArgumentException;

class Service
{
	
	protected $di;
	protected $name;
	protected $value;
	protected $shared = false;
	protected $callback;
	
	public function __construct(DI $di, $name) {
		$this->di = $di;
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function isShared() {
		return $this->shared;
	}
	
	public function share($value) {
		
		$this->shared = true;
		
		if ($value instanceof Closure) {
			$this->callback = $value;
		} else {
			$this->value = $value;
		}
	}
	
	public function factory($factory) {
		
		if (! is_object($factory) || ! method_exists($factory, '__invoke')) {
			throw new InvalidArgumentException("Factory must be Closure or invokable object.");
		}
		
		if ($factory instanceof DiAwareInterface) {
			$factory->setDI($this->di);
		}
		
		$this->callback = $factory;
	}
	
	public function resolve(array $args = null) {
		
		if (isset($this->value)) {
			return $this->value;
		}
		
		if (! isset($this->callback)) {
			return null;
		}
		
		$callback = $this->callback;
			
		if (isset($args)) {
			foreach($args as $arg => &$v) {
				if ($arg[0] === '@') {
					$v = $this->di->get(ltrim($arg, '@'));
				}
			}
			$value = call_user_func_array($callback, $args);
		} else {
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
	