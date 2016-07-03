<?php

namespace WordPress\Post;

trait DecoratorTrait
{
	
	protected $object;
	
	public function decorate($object) {
		if (! is_object($object)) {
			throw new \InvalidArgumentException("Expecting object, given: ".gettype($object));
		}
		$this->object = $object;
	}
	
	public function getObject() {
		return $this->object;
	}
	
	public function __get($key) {
		return isset($this->object->$key) ? $this->object->$key : null;
	}
	
	public function __isset($key) {
		return isset($this->object->$key);
	}
	
	public function __set($key, $value) {
		$this->object->$key = $value;
	}
	
	public function __unset($key) {
		if (isset($this->object->$key)) {
			unset($this->object->$key);
		}
	}
	
	public function __call($func, array $args) {
		if (is_callable($this->object, $func)) {
			return call_user_func_array(array($this->object, $func), $args);
		}
		throw new \BadMethodCallException("Invalid method '$func'");
	}
	
}

class Decorator
{
	
	use DecoratorTrait;
	
	public function __construct(\WP_Post $post) {
		$this->decorate($post);
	}
	
}
