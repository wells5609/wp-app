<?php

namespace WordPress\Events;

use Closure;

class BoundAction
{
	
	/**
	 * The object to which Closure and string callbacks will be bound.
	 * 
	 * @var object
	 */
	private $boundObject;
	
	public function bind($object) {
		if (! is_object($object)) {
			throw new \InvalidArgumentException("Can only bind to object, given: ".gettype($object));
		}
		$this->boundObject = $object;
	}
	
	public function isBound() {
		return isset($this->boundObject);
	}
	
	public function add($tag, $callback, $priority = 10, $num_args = 1) {
		add_action($tag, $this->bindCallback($callback), $priority, $num_args);
	}
	
	protected function bindCallback($callback) {
		if ($this->isBound()) {
			if (is_string($callback)) {
				return array($this->boundObject, $callback);
			} else if ($callback instanceof Closure) {
				return $callback->bindTo($this->boundObject, get_class($this->boundObject));
			}
		}
		return $callback;
	}
	
}
