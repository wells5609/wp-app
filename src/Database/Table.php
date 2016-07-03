<?php

namespace WordPress\Database;

use WordPress\Attribute\SerializeProperties;
use WordPress\Model\StorageInterface as ModelStorage;
use WordPress\Database\Table\Command\Alter as AlterCommand;
use InvalidArgumentException;

class Table implements ModelStorage
{
	
	use SerializeProperties;
	
	/**
	 * @var unknown $name
	 */
	protected $name;
	
	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var \WordPress\Database\Connection
	 */
	protected $connection;

	/**
	 * @var \WordPress\Database\Table\Column[]
	 */
	protected $columns;
	
	/**
	 * @var \WordPress\Database\Table\Schema
	 */
	protected $schema;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Database\Connection $connection
	 * @param string $table_name
	 * 
	 * @throws \InvalidArgumentException if $table_name is not a string.
	 */
	public function __construct(Connection $connection, $table_name) {
		if (! is_string($table_name)) {
			throw new InvalidArgumentException("Expecting string for 'table_name', given: ".gettype($table_name));
		}
		$this->connection = $connection;
		$this->tableName = $this->connection->maybeAddTablePrefix($table_name);
		if ($this->tableName !== $table_name) {
			// prefix was added
			$this->name = $table_name;
		} else {
			// $table_name is prefixed
			$this->name = $this->connection->stripTablePrefix($table_name);
		}
	}
	
	/**
	 * Returns a new Alter command associated with the table.
	 * 
	 * @return \WordPress\Database\Table\Command\Alter
	 */
	public function alter() {
		return new AlterCommand($this);
	}
	
	public function getSchema() {
		if (! isset($this->schema)) {
			$cols = array();
			foreach($this->getColumns() as $column) {
				$cols[$column->name] = $column->toSqlString();
			}
			$this->schema = new Table\Schema($this->name, $cols, $this->getPrimaryKeyColumn()->name);
		}
		return $this->schema;
	}
	
	/**
	 * Returns the table name, with global prefix.
	 * 
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}
	
	/**
	 * Returns the table name, without the global prefix.
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns the database connection.
	 * 
	 * @return \WordPress\Database\Connection
	 */
	public function getConnection() {
		return $this->connection;
	}
	
	/**
	 * Checks whether the table actually exists in the database.
	 * 
	 * @return boolean
	 */
	public function isInstalled() {
		return $this->connection->isTableInstalled($this->tableName);
	}

	/**
	 * Returns an array of Column objects for the table.
	 *
	 * @return array
	 */
	public function getColumns() {
		if (! isset($this->columns)) {
			$this->columns = $this->fetchColumns();
		}
		return $this->columns;
	}
	
	/**
	 * Returns a table column by name.
	 *
	 * @param string $name
	 *
	 * @return \WordPress\Database\Table\Column
	 */
	public function getColumn($name) {
		$columns = $this->getColumns();
		return isset($columns[$name]) ? $columns[$name] : null;
	}
	
	/**
	 * Returns the Column object for the table's primary key.
	 *
	 * @return \WordPress\Database\Table\Column
	 */
	public function getPrimaryKeyColumn() {
		foreach($this->getColumns() as $column) {
			if ($column->is_primary_key) {
				return $column;
			}
		}
	}

	/**
	 * Checks whether the given name is a column in the table.
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function isColumn($name) {
		$columns = $this->getColumns();
		return isset($columns[$name]);
	}
	
	/**
 	 * Queries the database for multiple records.
	 *
	 * @param array $where
	 * 
	 * @return mixed
	 */	
	public function find(array $where) {
		return $this->connection->find($this, $where, '*');
	}
	
	/**
 	 * Queries the database for a single record.
	 *
	 * @param array $where
	 * 
	 * @return mixed
	 */
	public function findOne(array $where) {
		return $this->connection->findOne($this, $where, '*');
	}
	
	/**
	 * Locates and returns a record by a field.
	 *
	 * @param string $field 
	 * 		Name of the field to search by (e.g. in a relational database, this would be a column). 
	 * @param string $value 
	 * 		The column value to search for.
	 * @param array $where [Optional] 
	 * 		Associative array of extra `WHERE` clause arguments.
	 * 
	 * @return mixed
	 */
	public function findOneBy($column, $value, array $where = array()) {
		return $this->connection->findOneBy($this, $column, $value, '*', $where);
	}
	
	/**
	 * Insert a row into a table.
	 * @see wpdb::insert()
	 */
	public function insert(array $data) {
		return $this->connection->insert($this, $data);
	}
	
	/**
	 * Update a row in the table.
	 * @see wpdb::update()
	 */
	 public function update(array $data, array $where) {
		return $this->connection->update($this, $data, $where);
	}
	
	/**
	 * Delete a row in the table.
	 * @see wpdb::delete()
	 */
	public function delete(array $where) {
		return $this->connection->delete($this, $where);
	}
	
	/**
	 * Returns the table name.
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->tableName;
	}
	
	/**
	 * Fetches column metadata from the database.
	 * 
	 * @return array
	 */
	protected function fetchColumns() {
		$cache_key = 'wpapp_db_col_'.get_current_blog_id().$this->tableName;
		$cached = wp_cache_get($cache_key, 'dbmeta', false, $found);
		if ($found && $cached) {
			return $cached;
		}
		$results = (array)$this->connection->wpdb()->get_results(
			"select C.COLUMN_NAME, C.COLUMN_DEFAULT, C.DATA_TYPE, C.CHARACTER_MAXIMUM_LENGTH, "
			."C.NUMERIC_PRECISION, C.IS_NULLABLE, C.COLUMN_KEY, C.EXTRA, C.COLLATION_NAME "
			."from INFORMATION_SCHEMA.COLUMNS as C where C.TABLE_NAME = '$this->tableName'"
		);
		$columns = array();
		foreach($results as $data) {
			$column = new Table\Column($data);
			$columns[$column->name] = $column;
		}
		wp_cache_set($cache_key, $columns, 'dbmeta', 600);
		return $columns;
	}
	
}
