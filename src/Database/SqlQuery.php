<?php

namespace WordPress\Database;

class SqlQuery 
{
	
	public $select = '*';
	public $insert;
	public $update;
	public $replace;
	public $delete;
	public $from;
	public $where;
	public $order_by;
	public $group_by;
	public $join;
	public $limit;
	public $extra;
	
	public $queryString;
	
	/**
	 * @var \wpdb
	 */
	protected $db;
	
	/**
	 * @var \WordPress\Database\Table\Schema
	 */
	protected $schema;
	
	public function __construct(Table\Schema $schema = null) {
		global $wpdb;
		$this->db = $wpdb;
		$this->schema = $schema;
	}
	
	public function select($selection) {
		if (is_array($selection)) {
			$selection = implode(', ', $selection);
		}
		$this->select = $selection;
		return $this;		
	}
	
	public function from($table_name) {
		$this->from = $table_name;
		return $this;
	}
	
	public function where(array $args) {
		$_where = array();
		foreach($args as $k => $v) {
			// Numeric key => value is SQL string (e.g. "col LIKE %val")
			if (is_numeric($k)) {
				$_pieces = explode(' ', $v);
				$key = $_pieces[0];
				$operator = $_pieces[1];
				$value = trim(str_replace("{$key} {$operator}", '', $v));
				$where = $this->formatWhere($key, $value, $operator);
				if (! empty($where)) {
					$_where[] = $where;
				}
			} else {
				$_where[] = $this->formatWhere($k, $v);
			}
		}
		$this->where = implode(' AND ', $_where);
		return $this;		
	}
	
	public function limit($num) {
		$this->limit = (int)$num;
		return $this;	
	}
	
	public function __toString() {
		
		$table = $this->getTableName();
		
		if (null === $table) {
			throw new \RuntimeException("Missing 'from' argument (table) and no Schema present.");
		}
		
		$sql = "SELECT {$this->select} FROM $table";			
		
		if (isset($this->where)) {
			$sql .= " WHERE {$this->where}";
		}
		
		if (isset($this->limit)) {
			$sql .= " LIMIT {$this->limit}";
		}
		
		if (isset($this->order_by)) {
			$sql .= " ORDER BY {$this->order_by}";
		}
		
		$this->queryString = $sql . ';';
		
		return $this->queryString;
	}
	
	public function orderBy($field, $order = 'ASC') {
		$order = strtoupper($order);
		$this->order_by = "`{$this->getTableName()}`.`$field` $order";
	}
	
	protected function getTableName() {
		if (isset($this->from)) {
			return $this->from;
		} else if (isset($this->schema)) {
			return $this->schema->table_name;
		}
		return null;
	}
	
	protected function formatWhere($key, $value, $operator = '=') {	
		if (is_string($value)) {
			$value = "'{$value}'";
		}
		return "`{$key}` {$operator} {$value}";
	}
	
}
