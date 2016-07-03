<?php

namespace WordPress\Attribute;

trait PropertyArrayAccess
{
	
	private $readOnlyProperties = array();
	
	public function offsetGet($key) {
		return isset($this->$key) ? $this->$key : null;
	}
	
	public function offsetSet($key, $value) {
		if ($this->readOnlyProperties && in_array($key, $this->readOnlyProperties, true)) {
			throw new \OutOfBoundsException("Attempting to set read-only property '$key'");
		}
		$this->$key = $value;
	}
	
	public function offsetExists($key) {
		return isset($this->$key);
	}
	
	public function offsetUnset($key) {
		if ($this->readOnlyProperties && in_array($key, $this->readOnlyProperties, true)) {
			throw new \OutOfBoundsException("Attempting to set read-only property '$key'");
		}
		unset($this->$key);
	}
	
}
