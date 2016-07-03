<?php

namespace WordPress\Storage;

use WordPress\Database\Table;

class Database implements StorageInterface
{
	
	/**
	 * The database table.
	 * 
	 * @var \WordPress\Database\Table
	 */
	protected $table;
	
	/**
	 * The record class name.
	 * 
	 * @var string
	 */
	protected $classname;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Database\Table $table
	 * @param string $recordClass
	 */
	public function __construct(Table $table, $recordClass) {
		$this->table = $table;
		$this->classname = $recordClass;
	}

	/** -----------------------------------------------
	 *  Implements StorageInterface
	 * --------------------------------------------- */
	
	/**
	 * Returns an alphanumeric string describing the type of storage container.
	 * 
	 * @return string
	 */
	public function getTypeName() {
		return 'database';
	}
	
	/**
	 * Returns the table name.
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->table->getName();
	}

	/**
 	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 * 
	 * @return mixed
	 */
	public function fetch($args) {
		$result = $this->table->find($this->getArrayArgs($args));
		return empty($result) ? array() : array_map(array($this, 'create'), $result);
	}
	
	/**
 	 * Retrieves a single record from storage.
	 *
	 * @param mixed $args
	 * 
	 * @return mixed
	 */
	public function fetchOne($args) {
		$result = $this->table->findOne($this->getArrayArgs($args));
		return empty($result) ? null : $this->create($result);
	}
	
	/**
	 * Delete a row in the table.
	 * 
	 * @see wpdb::delete()
	 */
	public function delete(RecordInterface $record) {
		return $this->table->delete($this->getArrayArgs($args));
	}
	
	/**
	 * Saves a row in the table.
	 * 
	 * @see wpdb::update()
	 */
	public function save(RecordInterface $record) {
		$dataArray = $this->getArrayArgs($data);
		$pkField = $this->table->getPrimaryKeyColumn()->name;
		$pk = $record->$pkField;
		if (! $pk) {
			return $this->table->insert($dataArray);
		}
		return $this->table->update($dataArray, array($pkField => $pk));
	}
	
	/**
	 * Creates a record from data.
	 *
	 * @param array $data
	 *
	 * @return RecordInterface
	 */
	public function create($data) {
		$class = $this->classname;
		return new $class($data);
	}
	
	/**
	 * Returns the arguments as an array.
	 * 
	 * If a non-array argument is passed, an array will be created using the table's
	 * primary column name as the key with the given argument as the (only) value.
	 * 
	 * @param mixed $args
	 * 
	 * @return array
	 */
	protected function getArrayArgs($args) {
		if ($args instanceof RecordInterface) {
			return $args->getModelData();
		}
		if (is_array($args)) {
			return $args;
		}
		return array($this->table->getPrimaryKeyColumn()->name => $args);
	}
	
}
