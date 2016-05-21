<?php

namespace WordPress\Data;

use BadMethodCallException;

/**
 * Abstract implementation of an WordPress object model entity.
 */
abstract class Entity implements EntityInterface
{
	
	/**
	 * Construct the entity optionally with initial data.
	 * 
	 * @param mixed $data [Optional]
	 */
	public function __construct($data = null) {
		if (isset($data)) {
			$this->hydrate($data);
		}
		$this->onConstruct();
	}
	
	/**
	 * Returns the entity as an associative array.
	 * 
	 * @return array
	 */
	public function toArray() {
		return get_object_vars($this);
	}
	
	/**
	 * Returns an array of data to save to the entity's storage repository.
	 * 
	 * @return array
	 */
	public function getStorageData() {
		$data = array();
		foreach($this->columnMap() as $property => $column) {
			$data[$column] = $this->$property;
		}
		return $data;
	}
	
	/**
	 * Hydrates the entity with the given data.
	 * 
	 * @param mixed $data
	 */
	public function hydrate($data) {
		if (! $data instanceof \Traversable && ! is_array($data)) {
			$data = is_object($data) ? get_object_vars($data) : (array)$data;
		}
		foreach($data as $key => $value) {
			$this->$key = $value;	
		}
		$this->onHydrate();
	}
	
	/**
	 * Returns a map (assoc. array) of the entity's property and storage column names.
	 * 
	 * @return array
	 */
	public function columnMap() {
		$columns = array_keys(get_object_public_vars($this));
		return array_combine($columns, $columns);
	}
	
	/* ----------------------------------------------------------------------------
	 *  Implements "property-access"
	 * 
	 *  Allows properties to be accessed and mutated by camelcased method.
	 * 
	 *  e.g. $name could be accessed via getName() and mutated via setName()
	 * ------------------------------------------------------------------------- */
	
	/**
	 * Magically handle property getters and setters.
	 *
	 * @param string $function
	 * @param array $arguments
	 * 
	 * @return mixed
	 */
	public function __call($function, array $args) {
		if (0 === strpos($function, 'get')) {
			$var = lcfirst(substr($function, 3));
			return $this[$var];
		}
		if (0 === strpos($function, 'set')) {
			$var = lcfirst(substr($function, 3));
			$this[$var] = $args[0];
		}
		throw new BadMethodCallException("Unknown method: '$function'");
	}
	
	/* ------------------------------------------------
	 *  Implements \Serializable
	 * --------------------------------------------- */
	
	/**
	 * Serializes the object.
	 * 
	 * @return string
	 */
	public function serialize() {
		return serialize($this->toArray());
	}
	
	/**
	 * Unserializes the object.
	 * 
	 * @param string
	 */
	public function unserialize($serial) {
		$this->hydrate(unserialize($serial));
	}
	
	/* ------------------------------------------------
	 *  Magic methods
	 * --------------------------------------------- */
	
	/**
	 * Returns a property value.
	 * 
	 * @param string $var
	 * 
	 * @return mixed
	 */
	public function __get($var) {
		if (method_exists($this, 'get'.$var)) {
			return $this->{'get'.$var}();
		}
		return $this->propertyGet($var);
	}
	
	/**
	 * Sets a property value.
	 * 
	 * @param string $var
	 * @param mixed $value
	 */
	public function __set($var, $value) {
		if (method_exists($this, 'set'.$var)) {
			$this->{'set'.$var}($value);
		} else {
			$this->propertySet($var, $value);
		}
	}
	
	/**
	 * Checks whether a property exists.
	 * 
	 * @param string $var
	 * 
	 * @return boolean
	 */
	public function __isset($var) {
		if (method_exists($this, 'get'.$var)) {
			return $this->{'get'.$var}() !== null;
		}
		return $this->propertyExists($var);
	}
	
	/**
	 * Unsets a property value.
	 * 
	 * @param string $var
	 */
	public function __unset($var) {
		if (method_exists($this, 'set'.$var)) {
			$this->{'set'.$var}(null);
		} else {
			$this->propertyUnset($var);
		}
	}
	
	/* ------------------------------------------------
	 *  Implements \ArrayAccess
	 * --------------------------------------------- */
	
	/**
	 * Returns a property value.
	 * 
	 * @param string $var
	 * 
	 * @return mixed
	 */
	public function offsetGet($var) {
		return $this->__get($var);
	}
	
	/**
	 * Sets a property value.
	 * 
	 * @param string $var
	 * @param mixed $value
	 */
	public function offsetSet($var, $value) {
		$this->__set($var, $value);
	}
	
	/**
	 * Checks whether a property exists.
	 * 
	 * @param string $var
	 * 
	 * @return boolean
	 */
	public function offsetExists($var) {
		return $this->__isset($var);
	}
	
	/**
	 * Unsets a property value.
	 * 
	 * @param string $var
	 */
	public function offsetUnset($var) {
		$this->__unset($var);
	}
	
	/* ------------------------------------------------
	 *  Property accessors & mutators
	 * --------------------------------------------- */
	
	/**
	 * Returns a property value.
	 * 
	 * @param string $property
	 * 
	 * @return mixed
	 */
	protected function propertyGet($property) {
		return property_exists($this, $property) ? $this->$property : null;
	}
	
	/**
	 * Sets a property value.
	 * 
	 * @param string $property
	 * @param mixed $value
	 */
	protected function propertySet($property, $value) {
		$this->$property = $value;
	}
	
	/**
	 * Checks whether a property exists.
	 * 
	 * @param string $property
	 * 
	 * @return boolean
	 */
	protected function propertyExists($property) {
		return property_exists($this, $property);
	}
	
	/**
	 * Unsets a property value.
	 * 
	 * @param string $property
	 */
	protected function propertyUnset($property) {
		unset($this->$property);
	}
	
	/* ------------------------------------------------
	 *  Misc. protected methods
	 * --------------------------------------------- */
	
	/**
	 * Called at the end of __construct()
	 */
	protected function onConstruct() {}
	
	/**
	 * Called at the end of hydrate()
	 */
	protected function onHydrate() {}
	
}
