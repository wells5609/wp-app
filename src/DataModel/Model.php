<?php

namespace WordPress\DataModel;

use WordPress\Database\Table\Schema;
use RuntimeException;
use InvalidArgumentException;

class Model implements \Serializable
{
	
	/**
	 * @var \WordPress\Database\Table\Schema
	 */
	protected $schema;
	
	/**
	 * @var \wpdb
	 */
	protected $db;
	
	public function __construct(Schema $schema) {
		$this->init($schema);
	}
	
	/**
	 * Returns the Schema
	 * @return \WordPress\Database\Table\Schema
	 */
	public function getSchema() {
		return $this->schema;
	}
	
	/**
 	 * Queries the database using multiple fields (i.e. column)
	 *
	 * @param array $where Array of "column" => "value" args
	 * @param string $select The SQL "SELECT" string
	 * @return mixed
	*/	
	public function find(array $where, $select = '*') {
		
		$sql_wheres = $sql_args = array();
		
		foreach($where as $field => $arg) {
			
			if (empty($arg)) {
				throw new RuntimeException('SQL Error: empty "'.$field.'" in '.__FUNCTION__);
			}
			
			if (! $this->schema->isColumn($field)) {
				throw new InvalidArgumentException('Field must be a valid table column');
			}
			
			$sql_wheres[] = "{$field} = ".$this->schema->getColumnFormatString($field);
			$sql_args[] = $arg;
		}
		
		if (is_array($select)) {
			$select = implode(', ', $select);
		}
		
		$sql = "SELECT $select FROM {$this->schema->table_name} WHERE ".implode(" AND ", $sql_wheres);
		
		$results = $this->db->get_results($this->db->prepare($sql, $sql_args));
		
		if (empty($results)) {
			return $results;
		}
		
		return array_map(array($this, 'forgeObject'), $results);
	}
	
	/**
 	 * Queries the database using multiple fields (i.e. column)
	 *
	 * @param array $where Array of "column" => "value" args
	 * @param string $select The SQL "SELECT" string
	 * @return mixed
	*/	
	public function findOne(array $where, $select = '*') {
		
		$sql_wheres = $sql_args = array();
		
		foreach($where as $field => $arg) {
			
			if (empty($arg)) {
				throw new RuntimeException('SQL Error: empty "'.$field.'" in '.__FUNCTION__);
			}
			
			if (! $this->schema->isColumn($field)) {
				throw new InvalidArgumentException('Field must be a valid table column');
			}
			
			$sql_wheres[] = "{$field} = ".$this->schema->getColumnFormatString($field);
			$sql_args[] = $arg;
		}
		
		if (is_array($select)) {
			$select = implode(', ', $select);
		}
		
		$sql = "SELECT $select FROM {$this->schema->table_name} WHERE ".implode(" AND ", $sql_wheres);
		
		$results = $this->db->get_row($this->db->prepare($sql, $sql_args));
		
		return empty($results) ? $results : $this->forgeObject($results);
	}
	
	/**
	 * Queries the database using a post extension field (i.e. column)
	 *
	 * @param string $field The field (column) to query by
	 * @param string $field_where The arguments for the field queried by
	 * @param string $select The SQL "SELECT" string
	 * @param array $extra_where Additional "WHERE" arguments as assoc. array.
	 * @return mixed
	*/	
	public function findOneBy($column, $column_where, $select = '*', array $extra_where = array()) {
					
		if (! $this->schema->isColumn($column)) {
			throw new InvalidArgumentException('Invalid field ' . $column . 'field must be a valid table column');
		}
		
		$sql_args = array(
			$column_where,
		);
		
		if (is_array($select)) {
			$select = implode(', ', $select);
		}
		
		$colFmtStr = $this->schema->getColumnFormatString($column);
		
		$sql = "SELECT $select FROM {$this->schema->table_name} WHERE $column = $colFmtStr";
		
		if (! empty($extra_where)) {
			
			$sql .= ' AND ';
			$wheres = $where_vals = array();
			
			foreach($extra_where as $col => $val) {
				if (empty($val)) {
					throw new RuntimeException('SQL Error: empty '.$col.' in '.__FUNCTION__);
				}
				$format = $this->schema->getColumnFormatString($col);
				$wheres[] = "$col = $format";
				$where_vals[] = $val;
			}
			
			$sql .= implode(" AND ", $wheres);
			
			$sql_args = array_merge($sql_args, $where_vals);
		}
		
		$results = $this->db->get_row($this->db->prepare($sql, $sql_args));
		
		return empty($results) ? $results : $this->forgeObject($results);
	}
	
	/**
	 * Returns a row by primary key.
	 * 
	 * @param mixed $pk
	 * @param string $select [Optional] Default = "*"
	 * @return mixed
	 */
	public function findOneByPrimaryKey($pk, $select = '*') {
		if (is_array($select)) {
			$select = implode(', ', $select);
		}
		$sql = "SELECT $select FROM `{$this->schema->table_name}` WHERE `{$this->schema->primary_key}` = $pk";
		$results = $this->db->get_row($sql);
		return empty($results) ? $results : $this->forgeObject($results);
	}
	
	/**
	 * Updates a row column
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @param array $where
	 * @param boolean $force_exists [Optional] Default = true
	 * @return mixed
	 */
	public function updateVar($name, $value, array $where, $force_exists = true) {
		if (! $force_exists) {
			$exists = $this->findOne($where, $name);
			if ($exists) return false;
		}
		return $this->update(array($name => $value), $where);		
	}
	
	/**
	 * Insert a row into a table.
	 *
	 * @see wpdb::insert()
	 */
	public function insert($data, $format = null) {
		
		$this->before_insert($data, $format);
		
		$success = $this->db->insert($this->schema->table_name, $data, $format);
		
		$this->after_insert($success);
		
		return $success;
	}
		
	protected function before_insert(&$data, &$format) {}
	protected function after_insert(&$success) {}
	
	/**
	 * Replace a row into a table.
	 *
	 * @see wpdb::replace()
	 */
	public function replace($data, $format = null) {
		
		$this->before_replace($data, $format);
		
		$success = $this->db->replace($this->schema->table_name, $data, $format, 'REPLACE');
		
		$this->after_replace($success);		
		
		return $success;
	}

	protected function before_replace(&$data, &$format) {}
	protected function after_replace(&$success) {}
	
	/**
	 * Update a row in the table
	 *
	 * @see wpdb::update()
	 */
	 public function update($data, $where, $format = null, $where_format = null) {
		
		$this->before_update($data, $where, $format, $where_format);
		
		$success = $this->db->update($this->schema->table_name, $data, $where, $format, $where_format);
		
		$this->after_update($success);
		
		return $success;
	}

	protected function before_update(&$data, &$where, &$format, &$where_format) {}
	protected function after_update(&$success) {}
	
	/**
	 * Delete a row in the table
	 *
	 * @see wpdb::delete()
	 */
	public function delete($where, $where_format = null) {
		
		$this->before_delete($where, $where_format);
		
		$success = $this->db->delete($this->schema->table_name, $where, $where_format);
		
		$this->after_delete($success);
		
		return $success;
	}
	
	protected function before_delete(&$where, &$where_format) {}
	protected function after_delete(&$success) {}
	
	/**
	 * Retrieve one row from the database.
	 *
	 * Executes a SQL query and returns the row from the SQL result via forgeObject()
	 *
	 * @see wpdb::get_row()
	 */
	public function getRow($query = null, $output = OBJECT, $y = 0) {
		$row = $this->db->get_row($query, $output, $y);
		return $this->forgeObject($row);
	}
	
	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * @see wpdb::query()
	 */
	public function query($sql) {
		return $this->db->query($sql);
	}
	
	/**
	 * Retrieve one variable from the database.
	 *
	 * @see wpdb::get_var()
	 */
	public function getVar($query = null, $x = 0, $y = 0) {
		return $this->db->get_var($query, $x, $y);
	}
	
	/**
	 * Retrieve one column from the database.
	 *
	 * @see wpdb::get_col()
	 */
	public function getCol($query = null, $x = 0) {
		return $this->db->get_col($query, $x);
	}
	
	/**
	 * Retrieve an entire SQL result set from the database (i.e., many rows)
	 *
	 * @see wpdb::get_results()
	 */
	public function getResults($string, $output_type = OBJECT) {
		
		$results = $this->db->get_results($string, $output_type);
		
		if (is_object($results)) {
			return $this->forgeObject($results);
		}
		
		if (! is_array($results)) {
			return $results;
		}
		
		$list = array();
		foreach($results as $result) {
			if (is_object($result)) {
				$list[] = $this->forgeObject($result);	
			} else {
				$list[] = $result;
			}
		}
		
		return $list;
	}
	
	/**
	 * Creates and returns an object
	 *
	 * @param object $data Row data from the database
	 * @return object
	 */
	public function forgeObject($data) {
		if (! $data) {
			return null;
		}
		$class = $this->schema->object_class;
		$object = new $class($data);
		if ($object instanceof Entity) {
			$object->setModel($this);
		}
		return $object;
	}
	
	public function serialize() {
		return serialize(array('schema' => $this->schema));
	}
	
	public function unserialize($serial) {
		$data = unserialize($serial);
		$this->init($data['schema']);
	}
	
	protected function init(Schema $schema) {
		
		$this->db = $GLOBALS['wpdb'];
		$this->schema = $schema;
		
		if (! isset($this->schema->table_name)) {
			$this->schema->table_name = $this->db->prefix.$this->schema->name;
		}

		$schema->build();
	}
	
}
