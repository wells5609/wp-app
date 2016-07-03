<?php

namespace WordPress\Model;

use WordPress\App;
use RuntimeException;

abstract class AbstractModel implements ModelInterface
{
	
	/**
	 * @var \WordPress\Model\DefinitionInterface
	 */
	protected $definition;
	
	/**
	 * Constructor.
	 * 
	 * @param mixed $data [Optional]
	 * @return void
	 */
	public function __construct($data = null) {
		
		$this->definition = App::instance()->get('modelManager')->getDefinitionOf($this);
		
		if (isset($data)) {
			$this->import($data);
		} else {
			$this->onCreate();
		}
		
		$this->onConstruct();
	}
	
	/**
	 * Returns the model's name.
	 * 
	 * @return string
	 */
	public function getModelName() {
		return $this->definition->getName();
	}
	
	/**
	 * Returns the model's database table name, including any prefix.
	 * 
	 * Default is the model name.
	 * 
	 * @return string
	 */
	//public function getTableName() {
	//	return $this->definition->getTableName();
	//}
	
	/**
	 * Returns the model's primary key.
	 * 
	 * Default = "id"
	 * 
	 * @return string
	 */
	public function getPrimaryKey() {
		return $this->definition->getPrimaryKey();
	}
	
	/**
	 * Returns a map of properties to columns.
	 * 
	 * @return array
	 */
	public function getColumnMap() {
		return $this->definition->getColumnMap();
	}
	
	/**
	 * Checks if the model has a database table column with the given name.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function isColumn($name) {
		return in_array($name, $this->definition->getColumnMap(), true);
	}
	
	/**
	 * Checks whether the model has a property with the given name.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function isProperty($name) {
		return array_key_exists($name, $this->definition->getColumnMap());
	}
	
	/**
	 * Translates a property to its corresponding column.
	 * 
	 * @param string $property
	 * @return string|null
	 */
	public function propertyToColumn($property) {
		$map = $this->definition->getColumnMap();
		if (isset($map[$property])) {
			return $map[$property];
		}
		return in_array($property, $map, true) ? $property : null;
	}
	
	/**
	 * Translates a column to its corresponding property.
	 * 
	 * @param string $column
	 * @return string|null
	 */
	public function columnToProperty($column) {
		$map = $this->definition->getColumnMap();
		if (isset($map[$column])) {
			return $column;
		}
		return array_search($column, $map, true) ?: null;
	}
	
	/**
	 * Returns a property value by property or column name.
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function readAttribute($name) {
		if ($prop = $this->columnToProperty($name)) {
			return $this->$prop;
		}
	}
	
	/**
	 * Assigns a property value by property or column name.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function writeAttribute($name, $value) {
		if ($prop = $this->columnToProperty($name)) {
			$this->$prop = $value;
		}
	}
	
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
	 * @param string $serial
	 * @return void
	 */
	public function unserialize($serial) {
		$this->import(unserialize($serial));
	}
	
	/**
	 * Imports data into the object.
	 * 
	 * @param mixed $data
	 * @return void
	 */
	public function import($data) {
		if (! is_array($data)) {
			$data = is_object($data) ? get_object_vars($data) : (array)$data;
		}
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * Returns the model's data as an array.
	 * 
	 * @return array
	 */
	public function toArray() {
		return get_object_vars($this);
	}
	
	/**
	 * Returns an array of the model's table data.
	 * 
	 * @return array
	 */
	public function getModelData() {
		$data = array();
		foreach($this->getColumnMap() as $property => $column) {
			$data[$column] = $this->$property;
		}
		return $data;
	}
	
	/**
	 * Returns the value of the primary key.
	 * 
	 * @return mixed
	 */
	public function getPrimaryKeyValue() {
		$pk = $this->getPrimaryKey();
		return isset($this->$pk) ? $this->$pk : null;
	}
	
	/**
	 * Returns the related records of the given model.
	 * 
	 * @param string $type
	 * @return mixed
	 */
	public function getRelatedRecords($type) {
		if ($rel = $this->definition->getRelationship($type)) {
			return $rel->getRelatedRecords($this);
		}
	}
	
	/**
	 * Saves the record to the database.
	 * 
	 * @param array $data [Optional]
	 * @return boolean
	 */
	public function save(array $data = null) {
		
		if (isset($data)) {
			$this->import($data);
		}
		
		if (! $this->getPrimaryKeyValue()) {
			return (bool) $this->insert();
		}
		
		return (bool) $this->update();
	}
	
	/**
	 * Insert the record into the table.
	 * 
	 * @param array $data [Optional]
	 * @return mixed
	 */
	public function insert(array $data = null) {
		$this->beforeInsert();
		if (isset($data)) {
			$this->import($data);
		}
		$data = $this->getModelData();
		$result = $this->definition->getStorage()->insert($data);
		$this->afterInsert($result);
		return $result;
	}
	
	/**
	 * Update the record in the table.
	 * 
	 * @param array $data [Optional]
	 * @return mixed
	 */
	 public function update(array $data = null) {
		$this->beforeUpdate();
		if (isset($data)) {
			$this->import($data);
		}
		$data = $this->getModelData();
		$primaryKey = $this->getPrimaryKey();
		if (! isset($data[$primaryKey])) {
			throw new RuntimeException("Cannot update model: primary key value not set.");
		}
		$pk = $data[$primaryKey];
		unset($data[$primaryKey]);
		$result = $this->definition->getStorage()->update($data, [$primaryKey => $pk]);
		$this->afterUpdate($result);
		return $result;
	}
	
	/**
	 * Delete the record from the table.
	 * 
	 * @return mixed
	 */
	public function delete() {
		$this->beforeDelete();
		$data = $this->getModelData();
		$primaryKey = $this->getPrimaryKey();
		if (empty($data[$primaryKey])) {
			throw new RuntimeException("Cannot delete: missing value of primary key.");
		}
		$result = $this->definition->getStorage()->delete([$primaryKey => $data[$primaryKey]]);
		$this->afterDelete($result);
		return $result;
	}
	
	/**
	 * Creates a new instance of the model.
	 * 
	 * @param mixed $data
	 * @return \WordPress\Model\ModelInterface
	 */
	public static function forgeObject($data) {
		return empty($data) ? null : new static($data);
	}
	
	/**
 	 * Queries the database using multiple fields (i.e. column)
	 *
	 * @param array $where Array of "column" => "value" args
	 * @param string $select The SQL "SELECT" string
	 * @return mixed
	*/	
	public static function find(array $where) {
		$class = get_called_class();	
		if ($definition = App::instance()->get('modelManager')->getDefinition($class)) {
			$results = $definition->getStorage()->find($where);
			if (empty($results) || ! is_array($results)) {
				return $results;
			}
			return array_map($class.'::forgeObject', $results);
		}
	}
	
	/**
 	 * Queries the database using multiple fields (i.e. column)
	 *
	 * @param array $where Array of "column" => "value" args
	 * @param string $select The SQL "SELECT" string
	 * @return mixed
	*/	
	public static function findOne(array $where) {
		$class = get_called_class();
		if ($definition = App::instance()->get('modelManager')->getDefinition($class)) {
			$results = $definition->getStorage()->findOne($where);
			return $class::forgeObject($results);
		}
	}
	
	protected function onConstruct() {}
	protected function onCreate() {}
	protected function beforeInsert() {}
	protected function afterInsert($result) {}
	protected function beforeUpdate() {}
	protected function afterUpdate($result) {}
	protected function beforeDelete() {}
	protected function afterDelete($result) {}
	
}
