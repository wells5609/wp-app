<?php

namespace WordPress\DataModel;

use WordPress\DependencyInjection\Injectable;
use RuntimeException;

class Entity extends Injectable implements \ArrayAccess, \Countable, \Serializable
{
	
	/**
	 * @var \WordPress\DataModel\Model
	 */
	protected $model;
	
	/**
	 * Constructor
	 */
	public function __construct($data = null) {
		if (isset($data)) {
			$this->import($data);
		}
		if (method_exists($this, 'onConstruct')) {
			$this->onConstruct();
		}
	}
	
	/**
	 * Sets the Model
	 * @param \WordPress\DataModel\Model $model
	 */
	public function setModel(Model $model) {
		$this->model = $model;
	}
	
	/**
	 * Returns the Model
	 * @return \WordPress\DataModel\Model
	 */
	public function getModel() {
		return $this->model;
	}
	
	/**
	 * Inserts the current object data into the database.
	 * 
	 * @param array $data [Optional]
	 * @return boolean
	 */
	public function insert($data = null) {	
		if (! isset($this->model)) {
			throw new RuntimeException("Cannot insert: missing Model");
		}
		if (isset($data)) {
			$this->import($data);
		}
		return $this->model->insert($this->getDataForStorage());
	}
	
	/**
	* Magically handle getters and setters.
	*
	* @param string $function
	* @param array $arguments
	* @return mixed
	*/
	public function __call($function, array $args) {
		if (0 === strpos($function, 'get')) {
			$property = lcfirst(substr($function, 3));
			if (isset($this->$property)) {
				return $this->{$property};
			}
		} else if (0 === strpos($function, 'set')) {
			$property = lcfirst(substr($function, 3));
			$this->{$property} = $args[0];
		}
	}
	
	public function get($var) {
		return isset($this->$var) ? $this->$var : null;	
	}
	
	public function set($var, $value) {
		$this->$var = $value;
		return $this;
	}
	
	public function exists($var) {
		return isset($this->$var);
	}
	
	public function remove($var) {
		unset($this->$var);
		return $this;
	}
	
	public function offsetGet($var) {
		return $this->get($var);
	}
	
	public function offsetSet($var, $value) {
		$this->set($var, $value);
	}
	
	public function offsetExists($var) {
		return $this->exists($var);
	}
	
	public function offsetUnset($var) {
		$this->remove($var);
	}
	
	public function count() {
		return count(get_object_vars($this)) - 1;
	}
	
	public function serialize() {
		return serialize(get_object_vars($this));
	}
	
	public function unserialize($serial) {
		$this->import(unserialize($serial));
	}
	
	public function import($data) {
		foreach((array)$data as $key => $value) {
			$this->$key = $value;	
		}
		if (method_exists($this, 'onImport')) {
			$this->onImport();
		}
	}
	
	public function getDataForStorage() {
		$data = array();
		foreach(get_object_vars($this) as $key => $value) {
			if (substr($key, 0, 1) != '_') {
				$data[$key] = $value;
			}
		}
		unset($data['model'], $data['di']);
		return $data;
	}
	
}
