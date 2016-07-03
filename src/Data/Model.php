<?php

namespace WordPress\Data;

use WordPress\Common\Util;
use WordPress\Common\Object;

class Model implements ModelInterface
{
	
	/**
	 * @var \WordPress\Data\StorageInterface
	 */
	protected $_storage;

	/**
	 * Construct the entity optionally with initial data.
	 *
	 * @param mixed $data [Optional]
	 */
	public function __construct($data = null) {
		if ($data) {
			$this->hydrate($data);
		} else {
			$this->onCreate();
		}
		$this->onConstruct();
	}

	/**
	 * Sets the model's storage container.
	 *
	 * @param \WordPress\Data\StorageInterface $storage
	 */
	public function setModelStorage(StorageInterface $storage) {
		$this->_storage = $storage;
	}

	/**
	 * Returns the model's storage container.
	 *
	 * @return \WordPress\Data\StorageInterface
	 */
	public function getModelStorage() {
		if (! isset($this->_storage)) {
			if (! $storage = $this->locateStorage()) {
				throw new \RuntimeException("Cannot save model: could not locate Storage");
			}
			$this->_storage = $storage;
		}
		return $this->_storage;
	}
	
	/**
	 * Returns an array of data to save to storage.
	 *
	 * @return array
	 */
	public function getModelData() {
		$data = array();
		foreach($this->columnMap() as $property => $column) {
			if (isset($this->$property)) {
				$data[$column] = $this->$property;
			}
		}
		return $data;
	}

	/**
	 * Returns a map (assoc. array) of the model's property and storage column names.
	 *
	 * @return array
	 */
	public function columnMap() {
		$columns = array_keys(Object::getPublicVars($this));
		return array_combine($columns, $columns);
	}

	/**
	 * Hydrates the model with the given data.
	 *
	 * @param mixed $data
	 */
	public function hydrate($data) {
		$colMap = array_flip($this->columnMap());
		foreach(Util::iterate($data) as $key => $value) {
			if (isset($colMap[$key])) {
				$key = $colMap[$key];
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
	 * Saves the model to storage.
	 *
	 * @return boolean
	 */
	public function save() {
		return (bool)$this->getModelStorage()->save($this);
	}

	/**
	 * Returns the unique identifier for the model.
	 *
	 * @return int|string
	 */
	public function getId() {
		return isset($this->ID) ? $this->ID : 0;
	}
	
	/** -----------------------------------------------
	 *  Field methods
	 * --------------------------------------------- */
	
	/**
	 * Returns the value of a field.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function readField($key) {
		if (method_exists($this, 'get'.$key)) {
			return $this->{'get'.$key}();
		}
		return $this->readProperty($key);
	}
	
	/**
	 * Sets the value of a field.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function writeField($key, $value) {
		if (method_exists($this, 'set'.$key)) {
			$this->{'set'.$key}($value);
		} else {
			$this->writeProperty($key, $value);
		}
	}
	
	/**
	 * Checks whether the object has a given field.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function hasField($key) {
		if (method_exists($this, 'get'.$key)) {
			return $this->{'get'.$key}() !== null;
		}
		return $this->readProperty($key) !== null;
	}
	
	/** -----------------------------------------------
	 *  Protected methods
	 * --------------------------------------------- */
	
	/**
	 * Returns the value of a class property.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function readProperty($key) {
		return isset($this->$key) ? $this->$key : null;
	}
	
	/**
	 * Sets the value of a class property.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	protected function writeProperty($key, $value) {
		$this->$key = $value;
	}
	
	/**
	 * Called when __construct() is invoked with no arguments.
	 */
	protected function onCreate() {}
	
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
	 * Magically handles field getters and setters.
	 *
	 * @param string $function
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call($function, array $args) {
		if (0 === strpos($function, 'get')) {
			return $this->readField(lcfirst(substr($function, 3)));
		}
		if (0 === strpos($function, 'set') && isset($args[0])) {
			$this->writeField(lcfirst(substr($function, 3)), $args[0]);
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
		return $this->readField($var);
	}
	
	/**
	 * Sets a property value.
	 *
	 * @param string $var
	 * @param mixed $value
	 */
	public function __set($var, $value) {
		$this->writeField($var, $value);
	}
	
	/**
	 * Checks whether a property exists.
	 *
	 * @param string $var
	 *
	 * @return boolean
	 */
	public function __isset($var) {
		return $this->hasField($var);
	}
	
	/**
	 * Unsets a property value.
	 *
	 * @param string $var
	 */
	public function __unset($var) {
		$this->writeField($var, null);
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
		return $this->readField($var);
	}
	
	/**
	 * Sets a property value.
	 *
	 * @param string $var
	 * @param mixed $value
	 */
	public function offsetSet($var, $value) {
		$this->writeField($var, $value);
	}
	
	/**
	 * Checks whether a property exists.
	 *
	 * @param string $var
	 *
	 * @return boolean
	 */
	public function offsetExists($var) {
		return $this->hasField($var);
	}
	
	/**
	 * Unsets a property value.
	 *
	 * @param string $var
	 */
	public function offsetUnset($var) {
		$this->writeField($var, null);
	}

	/** -----------------------------------------------
	 *  Implements \Countable
	 * --------------------------------------------- */
	
	/**
	 * {@inheritDoc}
	 * @see Countable::count()
	 */
	public function count() {
		return count($this->toArray());
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

	/** -----------------------------------------------
	 *  Protected/internal methods
	 * --------------------------------------------- */
	
	/**
	 * Attempts to locate the storage container via the \WordPress class.
	 *
	 * @return \WordPress\Data\StorageInterface|null
	 */
	protected function locateStorage() {
		if (class_exists('WordPress', false)) {
			if ($dataManager = \WordPress::get('dataManager')) {
				if ($type = $dataManager->getModelType($this)) {
					return $type->getStorage();
				}
			}
		}
	}
	
}
