<?php

namespace WordPress\Database;

use wpdb;

class Connection
{

	/**
	 * The default instance.
	 *
	 * @var \WordPress\Database\Connection
	 */
	protected static $instance;
	
	/**
	 * WordPress database instance.
	 *
	 * @var \wpdb;
	 */
	protected static $db;
	
	/**
	 * Installed table names cache.
	 * 
	 * @var array
	 */
	protected $tableNames;
	
	/**
	 * Table instances.
	 * 
	 * Note that not because Table objects are "lazy-loaded", not all the
	 * installed tables will necessarily exist. @see Connection::getTables()
	 * 
	 * @var array
	 */
	protected $tables = array();
	
	/**
	 * Whether to throw an exception if a requested table does not exist.
	 * 
	 * @var boolean $tableNotExistsThrow
	 */
	protected $tableNotExistsThrow = true;
	
	/**
	 * Returns the default database connection.
	 * 
	 * @return \WordPress\Database\Connection
	 */
	public static function instance() {
		return static::$instance;
	}
	
	/**
	 * Returns the WordPress database.
	 * 
	 * @return \wpdb
	 */
	public static function wpdb() {
		return static::$db;
	}
	
	/**
	 * Constructor.
	 * 
	 * @param \wpdb $db
	 */
	public function __construct(wpdb $db) {
		if (! isset(static::$instance)) {
			static::$instance = $this;
			static::$db = $db;
		}
	}
	
	/**
	 * Prepends the global table prefix.
	 * 
	 * @param string $table
	 * 
	 * @return string
	 */
	public function addTablePrefix($table) {
		return $this->wpdb()->prefix.$table;
	}

	/**
	 * Strips the global table prefix.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	public function stripTablePrefix($table) {
		return substr((string)$table, strlen($this->wpdb()->prefix));
	}
	
	/**
	 * Returns the string with the global table prefix.
	 * 
	 * If the string does not start with the global table prefix, it will be prepended.
	 * 
	 * @param string $table
	 * 
	 * @return string
	 */
	public function maybeAddTablePrefix($table) {
		$name = (string)$table;
		$prefix = $this->wpdb()->prefix;
		if (substr($name, 0, strlen($prefix)) === $prefix) {
			return $name;
		}
		return $prefix.$name;
	}
	
	/**
	 * Returns a table name from a Table or string. 
	 * 
	 * Prepends the global table prefix if necessary.
	 *
	 * @param string $tablename
	 *
	 * @throws \RuntimeException if the table does not exist and $this->tableNotExistsThrow === true
	 *
	 * @return string
	 */
	public function getValidTableName($table) {
		$name = (string)$table;
		$tables = $this->getTableNames();
		if (isset($tables[$name])) {
			return $tables[$name];
		} else if (in_array($name, $tables, true)) {
			return $name;
		}
		if ($this->tableNotExistsThrow) {
			throw new \RuntimeException("Database table '$name' does not exist.");
		}
		return null;
	}
	
	/**
	 * Returns a Table by name.
	 * 
	 * @param string $name
	 * 
	 * @return \WordPress\Database\Table
	 */
	public function getTable($name) {
		if (! isset($this->tables[$name])) {
			$this->tables[$name] = new Table($this, $name);
		}
		return $this->tables[$name];
	}
	
	public function refreshTables() {
		foreach($this->tables as $name => &$table) {
			$table = new Table($this, $name);
		}
	}

	/**
	 * Returns an array of all the database Tables.
	 * 
	 * This function may hurt performance.
	 * 
	 * @return array
	 */
	public function getTables() {
		$tables = array();
		foreach($this->getTableNames() as $shortName => $name) {
			$tables[$shortName] = $this->getTable($shortName);
		}
		return $tables;
	}

	/**
	 * Returns an array of the installed table names.
	 *
	 * Values are the prefixed table names, and keys are the non-prefixed names.
	 *
	 * @param boolean $reset [Optional] Whether to re-query the database. Default = false
	 *
	 * @return array
	 */
	public function getTableNames($reset = false) {
		if (! isset($this->tableNames) || $reset === true) {
			$this->tableNames = array();
			$prefix = $this->wpdb()->prefix;
			$length = strlen($prefix);
			foreach ($this->wpdb()->get_col('SHOW TABLES', 0) as $table) {
				if (strpos($table, $prefix) === 0) {
					$this->tableNames[substr($table, $length)] = $table;
				}
			}
		}
		return $this->tableNames;
	}
	
	/**
	 * Checks whether a given database table is installed.
	 *
	 * @param string $name Name of table to check, with or without prefix.
	 * @param boolean $reset [Optional] Whether to re-query the database. Default = false
	 *
	 * @return boolean
	 */
	public function isTableInstalled($name, $reset = false) {
		$tables = $this->getTableNames($reset);
		return isset($tables[$name]) || in_array($name, $tables, true);
	}

	/**
	 * Returns the database table prefix.
	 *
	 * @see wpdb::$prefix
	 *
	 * @return string
	 */
	public function getTablePrefix() {
		return $this->wpdb()->prefix;
	}
	
	/**
	 * Returns the database `charset` setting, if any.
	 *
	 * @return string|null
	 */
	public function getCharset() {
		return $this->wpdb()->charset ?: null;
	}
	
	/**
	 * Returns the database `collate` setting, if any.
	 *
	 * @return string|null
	 */
	public function getCollate() {
		return $this->wpdb()->collate ?: null;
	}
	
	/**
 	 * Queries the database using multiple fields (i.e. column)
	 *
	 * @param string $tablename
	 * @param array $where Array of "column" => "value" args
	 * @param string $select The SQL "SELECT" string
	 * 
	 * @return mixed
	*/	
	public function find($tablename, array $where, $select = '*') {
		$sql_args = array();
		$sql = $this->buildSelectQuery($tablename, $select, $where, $sql_args);
		return $this->wpdb()->get_results($this->wpdb()->prepare($sql, $sql_args));
	}
	
	/**
 	 * Queries the database using multiple fields (i.e. column)
	 *
	 * @param string $tablename
	 * @param array $where Array of "column" => "value" args
	 * @param string $select The SQL "SELECT" string
	 * 
	 * @return mixed
	*/	
	public function findOne($tablename, array $where, $select = '*') {
		$sql_args = array();
		$sql = $this->buildSelectQuery($tablename, $select, $where, $sql_args);
		return $this->wpdb()->get_row($this->wpdb()->prepare($sql, $sql_args));
	}
	
	/**
	 * Queries the database using a post extension field (i.e. column)
	 *
	 * @param string $tablename
	 * @param string $field The field (column) to query by
	 * @param string $value The arguments for the field queried by
	 * @param string $select The SQL "SELECT" string
	 * @param array $where Additional "WHERE" arguments as assoc. array.
	 * 
	 * @return mixed
	*/	
	public function findOneBy($tablename, $column, $value, $select = '*', array $where = []) {
		
		$tablename = $this->getValidTableName($tablename);
		$sql_args = array($value);
		
		if (is_array($select)) {
			$select = implode(', ', $select);
		}
		
		$sql = "SELECT $select FROM $tablename WHERE $column = ".$this->getSqlValuePlaceholder($value);
		
		if (! empty($where)) {
			$sql .= ' AND ';
			$wheres = $vals = array();
			foreach($where as $field => $value) {
				$wheres[] = "$field = ".$this->getSqlValuePlaceholder($value);
				$vals[] = $value;
			}
			$sql .= implode(' AND ', $wheres);
			$sql_args = array_merge($sql_args, $vals);
		}
		
		return $this->wpdb()->get_row($this->wpdb()->prepare($sql, $sql_args));
	}
	
	/**
	 * Insert a row into a table.
	 * 
	 * @see wpdb::insert()
	 */
	public function insert($tablename, array $data, $format = null) {
		return $this->wpdb()->insert($this->getValidTableName($tablename), $data, $format);
	}
	
	/**
	 * Update a row in the table.
	 * 
	 * @see wpdb::update()
	 */
	 public function update($tablename, array $data, array $where) {
		return $this->wpdb()->update($this->getValidTableName($tablename), $data, $where);
	}
	
	/**
	 * Delete a row in the table.
	 * 
	 * @see wpdb::delete()
	 */
	public function delete($tablename, array $where) {
		return $this->wpdb()->delete($this->getValidTableName($tablename), $where);
	}
	
	/**
	 * Retrieve an entire SQL result set from the database (i.e., many rows)
	 * 
	 * @see wpdb::get_results()
	 */
	public function getResults($query) {
		return $this->wpdb()->get_results($query, OBJECT);
	}
	
	/**
	 * Run a database query.
	 * 
	 * @see wpdb::query()
	 */
	public function query($query) {
		return $this->wpdb()->query($query);
	}
	
	/**
	 * Builds a SELECT statement with optional WHERE clauses.
	 * 
	 * @param string $tablename
	 * @param string $select
	 * @param array $where [Optional]
	 * @param array &$where_args [Optional/Required if given $where]
	 * 
	 * @return string
	 */
	protected function buildSelectQuery($tablename, $select, array $where = [], array &$where_args = []) {
		if (is_array($select)) {
			$select = implode(', ', $select);
		}
		$sql = "SELECT $select FROM ".$this->getValidTableName($tablename);
		if (! empty($where)) {
			$sql .= ' '.$this->buildWhereString($where, $where_args);
		}
		return $sql;
	}
	
	/**
	 * Builds a WHERE statement with one or more clauses.
	 * 
	 * @param array $wheres
	 * @param array &$args
	 * @param boolean $or Whether to join clauses by 'OR' instead of 'AND' (default = false).
	 * 
	 * @return array
	 */
	protected function buildWhereString(array $where, array &$args = [], $or = false) {
		$sql_strings = array();
		$args = $this->buildWhere($where, $sql_strings);
		$joinBy = $or ? ' OR ' : ' AND ';
		return 'WHERE '.implode($joinBy, $sql_strings);
	}
	
	/**
	 * Builds an array of 'where' values and strings to use in an SQL query.
	 * 
	 * @param array $wheres
	 * @param array &$strings
	 * 
	 * @return array
	 */
	protected function buildWhere(array $wheres, array &$strings) {
		$args = array();
		foreach($wheres as $field => $value) {
			$strings[] = "$field = ".$this->getSqlValuePlaceholder($value);
			$args[] = $value;
		}
		return $args;
	}
	
	/**
	 * Returns the SQL placeholder string for the given value.
	 * 
	 * @param mixed $value
	 * 
	 * @return string One of "%s" (string/default), "%d" (int), or "%f" (float) 
	 */
	protected function getSqlValuePlaceholder($value) {
		if (is_int($value)) {
			return '%d';
		} else if (is_float($value)) {
			return '%f';
		} else {
			return '%s';	
		}
	}
	
}
