<?php

namespace WordPress\Database;

use WordPress\Data\RepositoryInterface;
use WordPress\Data\EntityInterface;
use WordPress\Database\Table\Schema;
use RuntimeException;
use InvalidArgumentException;

class Repository implements RepositoryInterface
{
	
	/**
	 * @var \WordPress\Database\Table\Schema
	 */
	protected $schema;
	
	/**
	 * @var \wpdb
	 */
	protected $db;
	
	/**
	 * Constructor.
	 */
	public function __construct(Schema $schema) {
		$this->initialize($schema);
	}
	
	/**
	 * Returns the Schema
	 * @return \WordPress\Database\Table\Schema
	 */
	public function getSchema() {
		return $this->schema;
	}
	
	/**
	 * Returns the repository's entity type name.
	 * 
	 * @return string
	 */
	public function getEntityTypeName() {
		return $this->schema->name;
	}
	
	/**
	 * Returns all entities matching $args.
	 * 
	 * @param mixed $where [Optional]
	 * 
	 * @return array
	 */
	public function find($where = null) {
		
		$sql_wheres = $sql_args = array();
		
		foreach((array)$where as $field => $arg) {
			$this->assertValidWhereArgument($field, $arg, __FUNCTION__);
			$sql_wheres[] = "{$field} = ".$this->schema->getColumnFormatString($field);
			$sql_args[] = $arg;
		}
		
		$sql = "SELECT * FROM {$this->schema->table_name} WHERE ".implode(' AND ', $sql_wheres);
		
		$results = $this->db->get_results($this->db->prepare($sql, $sql_args));
		
		if (empty($results)) {
			return array();
		}
		
		return array_map(array($this, 'forgeObject'), $results);
	}
	
	/**
	 * Returns a single entity matching $args.
	 * 
	 * @param mixed $where
	 * 
	 * @return \WordPress\Data\EntityInterface
	 */
	public function findOne($where) {
		
		$sql_wheres = $sql_args = array();
		
		foreach((array)$where as $field => $arg) {
			$this->assertValidWhereArgument($field, $arg, __FUNCTION__);
			$sql_wheres[] = "{$field} = ".$this->schema->getColumnFormatString($field);
			$sql_args[] = $arg;
		}
		
		$sql = "SELECT * FROM {$this->schema->table_name} WHERE ".implode(" AND ", $sql_wheres);
		
		$results = $this->db->get_row($this->db->prepare($sql, $sql_args));
		
		return empty($results) ? null : $this->forgeObject($results);
	}
	
	/**
	 * Saves the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function save(EntityInterface $entity) {
		
		$data = $entity->getStorageData();
		$pk = $this->schema->primary_key;
		
		if (! isset($data[$pk])) {
			return $this->insert($data);
		}
		
		$entityPkValue = $data[$pk];
		unset($data[$pk]);
		
		return $this->update($data, array($pk => $entityPkValue));
	}
	
	/**
	 * Deletes the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function delete(EntityInterface $entity) {
		$pk = $this->schema->primary_key;
		if (! isset($entity->$pk)) {
			throw new RuntimeException("Cannot delete entity: missing value of primary key ({$pk}).");
		}
		return $this->deleteWhere(array($pk => $entity->$pk));
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
					
		$this->assertValidWhereArgument($column, $column_where, __FUNCTION__);
		
		$sql_args = array($column_where);
		
		if (is_array($select)) {
			$select = implode(', ', $select);
		}
		
		$colFormatString = $this->schema->getColumnFormatString($column);
		
		$sql = "SELECT $select FROM {$this->schema->table_name} WHERE $column = $colFormatString";
		
		if (! empty($extra_where)) {
			
			$sql .= ' AND ';
			$wheres = $where_vals = array();
			
			foreach($extra_where as $col => $val) {				
				$this->assertValidWhereArgument($col, $val, __FUNCTION__);
				$format = $this->schema->getColumnFormatString($col);
				$wheres[] = "$col = $format";
				$where_vals[] = $val;
			}
			
			$sql .= implode(" AND ", $wheres);
			
			$sql_args = array_merge($sql_args, $where_vals);
		}
		
		$results = $this->db->get_row($this->db->prepare($sql, $sql_args));
		
		return empty($results) ? null : $this->forgeObject($results);
	}
	
	/**
	 * Returns a row by primary key.
	 * 
	 * @param mixed $pk
	 * @param string $select [Optional] Default = "*"
	 * @return mixed
	 */
	public function findOneByPrimaryKey($pk) {
		$sql = "SELECT * FROM `{$this->schema->table_name}` WHERE `{$this->schema->primary_key}` = $pk";
		$results = $this->db->get_row($sql);
		return empty($results) ? null : $this->forgeObject($results);
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
		return $this->db->insert($this->schema->table_name, $data, $format);
	}
	
	/**
	 * Replace a row into a table.
	 *
	 * @see wpdb::replace()
	 */
	public function replace($data, $format = null) {
		return $this->db->replace($this->schema->table_name, $data, $format, 'REPLACE');
	}

	/**
	 * Update a row in the table
	 *
	 * @see wpdb::update()
	 */
	 public function update($data, $where, $format = null, $where_format = null) {
		return $this->db->update($this->schema->table_name, $data, $where, $format, $where_format);
	}

	/**
	 * Delete a row in the table
	 *
	 * @see wpdb::delete()
	 */
	public function deleteWhere($where, $where_format = null) {
		return $this->db->delete($this->schema->table_name, $where, $where_format);
	}
	
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
			$object->setRepository($this);
		}
		
		return $object;
	}
	
	public function serialize() {
		return serialize(array('schema' => $this->schema));
	}
	
	public function unserialize($serial) {
		$data = unserialize($serial);
		$this->initialize($data['schema']);
	}
	
	protected function initialize(Schema $schema) {
		$this->db = $GLOBALS['wpdb'];
		$this->schema = $schema;
		if (! isset($this->schema->table_name)) {
			$this->schema->table_name = $this->db->prefix.$this->schema->name;
		}
		$schema->build();
	}
	
	protected function assertValidWhereArgument($field, $value, $source) {
		if (empty($value)) {
			throw new RuntimeException("SQL Error: empty value for '$field' in '$source'.");
		}
		if (! $this->schema->isColumn($field)) {
			throw new InvalidArgumentException("Invalid table column: '$field'");
		}
	}
	
}
