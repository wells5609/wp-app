<?php

namespace WordPress\Database\Table;

use WordPress\Database\Connection;

class Schema implements \Serializable
{
	
	const INTEGER = 'integer';
	const DOUBLE = 'double';
	const STRING = 'string';
	
	protected $name;
	protected $tableName;
	protected $primaryKey;
	protected $columns = array();
	protected $uniqueKeys = array();
	protected $keys = array();
	protected $columnTypes = [];
	protected $columnFormats = [];
	protected $installed = false;
	
	public function __construct($name, array $columns, $primary_key, array $unique_keys = [], array $keys = []) {
		
		global $wpdb;
		
		$this->name = trim($name);
		$this->table_name = $wpdb->prefix.$this->name;
		$this->columns = $columns;
		$this->primaryKey = $primary_key;
		$this->uniqueKeys = $unique_keys;
		$this->keys = $keys;
		
		foreach($this->columns as $name => $str) {
			if (stripos($str, 'int') !== false || stripos($str, 'time') !== false) {
				$this->columnTypes[$name] = static::INTEGER;
				$this->columnFormats[$name] = '%d';
			} else if (stripos($str, 'float') !== false) {
				$this->columnTypes[$name] = static::DOUBLE;
				$this->columnFormats[$name] = '%f';
			} else {
				$this->columnTypes[$name] = static::STRING;
				$this->columnFormats[$name] = '%s';
			}
		}
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getTableName() {
		return $this->tableName;
	}
	
	public function getColumns() {
		return $this->columns;
	}
	
	public function getPrimaryKey() {
		return $this->primaryKey;
	}
	
	public function getUniqueKeys() {
		return $this->uniqueKeys;
	}
	
	public function getKeys() {
		return $this->keys;
	}
	
	public function validate() {
		return ! empty($this->name) && ! empty($this->primaryKey);
	}
	
	public function isColumn($column) {
		return isset($this->columns[$column]);
	}
	
	public function getColumnType($column) {
		return isset($this->columnTypes[$column]) ? $this->columnTypes[$column] : null;
	}
	
	public function getColumnFormatString($column) {
		return isset($this->columnFormats[$column]) ? $this->columnFormats[$column] : null;
	}
	
	public function isColumnType($column, $type) {
		if (isset($this->columnTypes[$column])) {
			return $this->columnTypes[$column] === $type;
		}
		return false;
	}
	
	public function isColumnInt($column) {
		return $this->isColumnType($column, static::INTEGER);
	}
	
	public function isColumnDouble($column) {
		return $this->isColumnType($column, static::DOUBLE);
	}
	
	public function isColumnString($column) {
		return $this->isColumnType($column, static::STRING);
	}
	
	public function getColumnMaxLength($column) {
		if (! $this->isColumn($column)) {
			return false;
		}
		$field = $this->columns[$column];
		if (strpos($field, '(') === false) {
			return null;
		}
		$start = strpos($field, '(') + 1;
		$length = substr($field, $start, strpos($field, ')') - $start);
		// Floats can have two max lengths: (3,5) => 123.12345
		if (strpos($length, ',') !== false) {
			$length = array_sum(explode(',', $length));
		}
		return (int)$length;
	}
	
	public function isInstalled() {
		if (! isset($this->installed)) {
			$this->installed = Connection::instance()->isTableInstalled($this->tableName);
		}
		return $this->installed;
	}
	
	public function install() {
		if (! $this->isInstalled()) {
			$command = new Command\Create($this);
			$command();
			if (Connection::instance()->isTableInstalled($this->tableName, true)) {
				$this->installed = true;
			}
		}
		return $this->installed;
	}
	
	public function serialize() {
		return serialize(get_object_vars($this));
	}
	
	public function unserialize($serial) {
		foreach(unserialize($serial) as $key => $value) {
			$this->$key = $value;
		}
	}
	
}
