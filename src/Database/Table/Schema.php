<?php

namespace WordPress\Database\Table;

class Schema implements \Serializable
{
	const COLUMN_TYPE_INT = 'integer';
	const COLUMN_TYPE_DOUBLE = 'double';
	const COLUMN_TYPE_STRING = 'string';
	
	public $table_name;
	public $name;
	public $primary_key;
	public $columns = array();
	public $unique_keys = array();
	public $keys = array();
	public $object_class = 'WordPress\DataModel\Entity';
	public $auto_install = true;
	
	private $columnTypes;
	private $built = false;
	private $installed = false;
	
	public function validate() {
		return ! empty($this->name) && ! empty($this->primary_key) && ! empty($this->object_class);
	}
	
	public function isColumn($column) {
		return isset($this->columns[$column]);
	}
	
	public function getColumnType($column) {
		$this->build(false);
		return isset($this->columnTypes[$column]) ? $this->columnTypes[$column] : null;
	}
	
	public function getColumnFormatString($column) {
		switch ($this->getColumnType($column)) {
			case static::COLUMN_TYPE_INT:
				return '%d';
			case static::COLUMN_TYPE_DOUBLE:
				return '%f';
			case static::COLUMN_TYPE_STRING:
				return '%s';
			default:
				return null;
		}
	}
	
	public function getColumnMaxLength($column) {
		if (! $this->isColumn($column)) {
			return false;
		}
		$field = $this->columns[$column];
		if (strpos($field, '(') === false) {
			return null;
		}
		$_start = strpos($field, '(') + 1;
		$length = substr($field, $_start, strpos($field, ')') - $_start );
		// Floats can have two max lengths: (3,5) => 123.12345
		if (strpos($length, ',') !== false) {
			$arr = explode(',', $length);
			$length = array_sum($arr);
		}
		return (int)$length;
	}
	
	public function isColumnType($column, $type) {
		$this->build(false);
		if (isset($this->columnTypes[$column])) {
			return $this->columnTypes[$column] === $type;
		}
		return false;
	}
	
	public function isColumnInt($column) {
		return $this->isColumnType($column, static::COLUMN_TYPE_INT);
	}
	
	public function isColumnDouble($column) {
		return $this->isColumnType($column, static::COLUMN_TYPE_DOUBLE);
	}
	
	public function isColumnString($column) {
		return $this->isColumnType($column, static::COLUMN_TYPE_STRING);
	}
	
	public function build($rebuild = true) {
		global $wpdb;
		if (! $this->built || $rebuild === true) {
			if (! isset($this->table_name)) {
				$this->table_name = $wpdb->prefix.$this->name;
			}
			$this->detectColumnTypes();
			$this->detectTableInstallStatus();
			$this->built = true;
		}
	}
	
	public function isInstalled() {
		$this->build(false);
		return $this->installed;
	}
	
	public function install() {
		if (! $this->installed) {
			$alter = new Alter($this);
			if ($alter->install()) {
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
	
	protected function detectColumnTypes() {
		$this->columnTypes = array();
		foreach($this->columns as $name => $str) {
			if (stripos($str, 'int') !== false || stripos($str, 'time') !== false) {
				$this->columnTypes[$name] = static::COLUMN_TYPE_INT;
			} else if (stripos($str, 'float') !== false) {
				$this->columnTypes[$name] = static::COLUMN_TYPE_DOUBLE;
			} else {
				$this->columnTypes[$name] = static::COLUMN_TYPE_STRING;
			}
		}
	}
	
	protected function detectTableInstallStatus() {
		global $wpdb;
		foreach($wpdb->get_col('SHOW TABLES', 0) as $tbl) {
			if ($tbl === $this->table_name) {
				$this->installed = true;
				return;
			}
		}
		if (! $this->installed && $this->auto_install) {
			$this->install();
		}
	}
	
}
