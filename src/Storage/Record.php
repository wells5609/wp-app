<?php

namespace WordPress\Storage;

abstract class Record implements RecordInterface
{
	
	/**
	 * Returns a description of the model.
	 *
	 * @return string
	 */
	public function getModelDescription() {
		return '';
	}
	
	/**
	 * Returns an array of data to save to storage.
	 *
	 * @return array
	 */
	public function getModelData() {
		$data = array();
		foreach($this->columnMap() as $prop => $column) {
			if (isset($this->$prop)) {
				$data[$column] = $this->$prop;
			}
		}
		return $data;
	}
	
	/**
	 * Construct the entity optionally with initial data.
	 *
	 * @param mixed $data [Optional]
	 */
	public function __construct($data = null) {
		if ($data) {
			$this->hydrate($data);
		}
		$this->onConstruct();
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
		$colKeyMap = $this->columnMap();
		foreach($data as $key => $value) {
			if ($prop = array_search($key, $colKeyMap, true)) {
				$key = $prop;
			}
			$this->$key = $value;
		}
		$this->onHydrate();
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
	 * Returns a map (assoc. array) of the entity's property and storage column names.
	 *
	 * @return array
	 */
	public function columnMap() {
		$columns = array_keys(Object::getPublicVars($this));
		return array_combine($columns, $columns);
	}

	/**
	 * Returns a unique identifier for the object.
	 * 
	 * @return string
	 */
	public function getUid() {
		return md5(serialize($this));
	}
	
	/**
	 * Called at the end of __construct()
	 */
	protected function onConstruct() {}
	
	/**
	 * Called at the end of hydrate()
	 */
	protected function onHydrate() {}
	
	/** ---------------------------------------------------------------------------
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
			return $this->__get(lcfirst(substr($function, 3)));
		}
		if (0 === strpos($function, 'set') && isset($args[0])) {
			$this->__set(lcfirst(substr($function, 3)), $args[0]);
			return;
		}
		throw new \BadMethodCallException("Unknown method: '$function'");
	}
	
	/** -----------------------------------------------
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
		return isset($this->$var) ? $this->$var : null;
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
		} else if (property_exists($this, $var)) {
			$this->$var = $value;
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
		return isset($this->$var);
	}
	
	/**
	 * Unsets a property value.
	 *
	 * @param string $var
	 */
	public function __unset($var) {
		if (method_exists($this, 'set'.$var)) {
			$this->{'set'.$var}(null);
		} else if (isset($this->$var)) {
			unset($this->$var);
		}
	}
	
	/** -----------------------------------------------
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
	
	/** -----------------------------------------------
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
	
}
