<?php

class SqlSelectQuery extends SqlQuery {
	
	public $select = '*';
	
}

class SqlQuery {
	
	public 
		$select,
		$insert,
		$update,
		$replace,
		$delete,
		$from,
		$where,
		$order_by,
		$group_by,
		$join,
		$limit,
		$extra;
		
	public $_query;
	
	protected $db;
	
	protected $schema;
	
	function __construct( &$schema ){
		global $wpdb;
		$this->db =& $wpdb;
		$this->schema =& $schema;
	}
	
	public function select( $args ){
		if ( is_array($args) )
			$this->select = implode(', ', $args);
		else $this->select = $args;
		return $this;		
	}
	
	public function from( $args ){
		$this->from = $args;
		return $this;		
	}
	
	public function where( array $args ){
		$_where = array();
		foreach($args as $k => $v){
			// Numeric key => value is SQL string (e.g. "col LIKE %val")
			if ( is_numeric($k) ){
				$_pieces = explode(' ', $v);
				$key = $_pieces[0];
				$operator = $_pieces[1];
				$value = trim(str_replace("{$key} {$operator}", '', $v));
				$where = $this->formatWhere($key, $value, $operator);
				if ( !empty($where) )
					$_where[] = $where;
				continue;
			}
			$_where[] = $this->formatWhere($k, $v);
		}
		$this->where = implode(' AND ', $_where);
		return $this;		
	}
	
	public function limit( $arg ){
		$this->limit = $arg;
		return $this;	
	}
	
	public function __toString(){
		
		$sql = '';
		
		if ( isset($this->select) )
			$sql .= "SELECT {$this->select}";
		
		# Add other operations
		
		if ( isset($this->from) )
			$sql .= " FROM {$this->from}";			
		else 
			$sql .= " FROM {$this->schema->table}";
		
		if ( isset($this->where) )
			$sql .= " WHERE {$this->where}";
		
		if ( isset($this->limit) )
			$sql .= " LIMIT {$this->limit}";
		
		if ( isset($this->order_by) )
			$sql .= " ORDER BY {$this->order_by}";
		
		$sql .= ";";
		
		$this->_query = $sql;
		
		return $this->_query;
	}
		
	public function toString( &$return = null ){
		$return = $this->__toString();	
	}

	protected function formatWhere($key, $value, $operator = '='){
		
		$format = $this->schema->get_field_format($key);
		
		if ( !$format )
			return;
				
		if ( '%s' === $format )
			$value = "'{$value}'";
		
		return "`{$key}` {$operator} {$value}";
	}
	

	function order_by( $by, $order = 'ASC' ){
		
		$this->order_by = "`{$this->schema->table}`.`{$by}` {strtoupper($order)}";
	}
	
	function join( $type, $args, $on ){
		# TBD
	}
	
	function group_by( $args ){
		# TBD
		//	[GROUP BY {col_name | expr | position}
      	//		[ASC | DESC], ... [WITH ROLLUP]]
	}
	
	function having( $args ){
		//	[HAVING where_condition]	
	}
	
}
