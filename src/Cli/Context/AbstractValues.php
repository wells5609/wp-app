<?php

namespace WordPress\Cli\Context;

abstract class AbstractValues implements \Countable, \ArrayAccess
{

	protected $values;

	public function __construct(array $values = []) {
		$this->values = $values;
	}

	public function offsetGet($key) {
		return array_key_exists($key, $this->values) ? $this->values[$key] : null;
	}

	public function offsetExists($key) {
		return array_key_exists($key, $this->values);
	}

	public function offsetSet($key, $value) {
		$this->values[$key] = $value;
	}

	public function offsetUnset($key) {
		unset($this->values[$key]);
	}

	public function count() {
		return count($this->values);
	}

	public function get($key, $default = null) {
		$value = $this->offsetGet($key);
		return null !== $value ? $value : $default;
	}

	public function exists($key) {
		return $this->offsetExists($key);
	}

	public function toArray() {
		return $this->values;
	}

}
