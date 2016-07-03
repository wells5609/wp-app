<?php

namespace WordPress\Storage\Database;

class Table
{
	
	/**
	 * Database table name.
	 * 
	 * @var string
	 */
	protected $name;
	
	/**
	 * Column objects for the table.
	 * 
	 * @var array
	 */
	protected $columns;
	
	/**
	 * Constructor.
	 * 
	 * @param string $name
	 */
	public function __construct($name) {
		global $wpdb;
		if (strpos($name, $wpdb->prefix) !== 0) {
			$name = $wpdb->prefix.$name;
		}
		$this->name = $name;
		$this->columns = array();
		foreach($this->getColumnData() as $data) {
			$column = new Table\Column($data);
			$this->columns[$column->name] = $column;
		}
	}
	
	/**
	 * Returns the full table name (with prefix).
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns the table name without prefix.
	 * 
	 * @return string
	 */
	public function getBasename() {
		global $wpdb;
		return substr($this->name, strlen($wpdb->prefix));
	}
	
	/**
	 * Returns a Column by name.
	 * 
	 * @param string $name
	 * 
	 * @return \WordPress\Storage\Database\Table\Column
	 */
	public function getColumn($name) {
		return isset($this->columns[$name]) ? $this->columns[$name] : null;
	}
	
	/**
	 * Checks whether the given column exists.
	 * 
	 * @param string $name
	 * 
	 * @return boolean
	 */
	public function isColumn($name) {
		return isset($this->columns[$name]);
	}
	
	/**
	 * Returns the table's primary key column.
	 * 
	 * @return \WordPress\Storage\Database\Table\Column
	 */
	public function getPrimaryKeyColumn() {
		foreach($this->columns as $column) {
			if ($column->is_primary_key) {
				return $column;
			}
		}
	}
	
	public function serialize() {
		return serialize(get_object_vars($this));
	}
	
	public function unserialize($serial) {
		foreach(unserialize($serial) as $key => $value) {
			$this->$key = $value;
		}
	}
	
	protected function getColumnData() {
		
		global $wpdb;
		
		$cacheKey = "wpext_database_table_columns_$this->name";
		
		if ($cached = \wp_cache_get($cacheKey)) {
			return $cached;
		}
		
		$query = "SELECT C.COLUMN_NAME, C.DATA_TYPE, C.CHARACTER_MAXIMUM_LENGTH, "
				."C.NUMERIC_PRECISION, C.IS_NULLABLE, C.COLUMN_KEY, C.EXTRA "
				."FROM INFORMATION_SCHEMA.COLUMNS AS C WHERE C.TABLE_NAME = '$this->name'";
		
		$columns = $wpdb->get_results($query);
		
		\wp_cache_set($cacheKey, $columns);
		
		return $columns;
	}
	
}
