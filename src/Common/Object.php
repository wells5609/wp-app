<?php

namespace WordPress\Common;

use WordPress\Attribute\SerializeProperties;
use WordPress\Attribute\PropertyArrayAccess;

class Object implements \ArrayAccess, \Countable, \Serializable
{
	
	use SerializeProperties;
	use PropertyArrayAccess;
	
	public function __construct($data) {
		if ($data) {
			$this->hydrate($data);
		}
	}
	
	public function hydrate($data) {
		if (! $data instanceof \Traversable && ! is_array($data)) {
			$data = is_object($data) ? get_object_vars($data) : (array)$data;
		}
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function toArray() {
		return get_object_vars($this);
	}
	
	public function count() {
		return count(get_object_vars($this));
	}
	
	public static function create($data = null) {
		return new static($data = null);
	}
	
	public static function asArray($data) {
		return $data instanceof \Traversable ? iterator_to_array($data) : get_object_vars($data);
	}
	
	public static function getPublicVars($object) {
		return get_object_vars($object);
	}
	
}