<?php
abstract class Model {
	
	/** 
	* The full prefixed table name
	* 
	* This is set by the constructor
	*/
	public $table;
	
	
	/** 
	* Unprefixed table name
	*
	* This must be set like 'bears' for 'wp_bears'
	* assuming prefix is wp_
	*/
	public $table_basename;
	
	
	/** $columns 
	* 
	* The table columns
	* 
	* Array of 'column' => 'SQL create string'
	* e.g. 'id' => 'bigint(20) unsigned NOT NULL auto_increment'
	*/
	public $columns = array();
	
	
	/** $primary_key
	*
	* Required string
	*/
	public $primary_key; ####TO-DO####
	
	
	/** $unique_keys 
	* 
	* Array of 'key_name' => 'column_name'
	*/
	public $unique_keys = array();
	
	
	/** $keys
	* 
	* Array of 'key_name' => 'column_name'
	*/
	public $keys = array();
	
	
	/**
	* @var $_object_class
	*
	* String representing the object class to use.
	*/
	public $_object_class = 'Object';
	
		
	/** Constructor	
	*
	* Sets up table name and wpdb
	*
	*/
		final function __construct(){
			
			global $wpdb;
			
			// set the table name
			$this->table = $wpdb->prefix . $this->table_basename;	
			
			// add table basename to $tables array
			if ( !in_array($this->table_basename, $wpdb->tables) )
				$wpdb->tables[] = $this->table_basename;
			
			// add the table name to $wpdb (as property)
			if ( !isset($wpdb->{$this->table_basename}) )
				$wpdb->{$this->table_basename} = $this->table;
			
			// add column formats that aren't strings
			foreach($this->columns as $col => $settings){
				$format = $this->get_column_format($col);
				if ( '%s' !== $format )
					$wpdb->field_types[ $col ] = $format;
			}
			
		}
	
	
	public function get_schema(){
		
		return array(
			'table' 			=> $this->table,
			'table_basename'	=> $this->table_basename,
			'columns'			=> $this->columns,
			'primary_key'		=> $this->primary_key,
			'unique_keys'		=> $this->unique_keys,
			'keys'				=> $this->keys,
		);	
	}
	
	
	/**
	* Returns a column's format for SQL 
	*
	* integer => %d
	* float => %f
	* string => %s (default)
	*/
		public function get_column_format($column){
			if ( !$this->is_column($column) )
				return false;
			
			$field = strtolower($this->columns[$column]);
			
			if ( strpos($field, 'int') !== false || strpos($field, 'time') !== false )
				return '%d';
			if ( strpos($field, 'float') !== false )
				return '%f';
			else
				return '%s';
		}
	
	public function is_column($column){
		return isset($this->columns[$column]) ? true : false;
	}
	
	/** 
	* Returns field length max
	*
	*/
		public function get_column_length($column){
			if ( !$this->is_column($column) )
				return false;
			
			$field = $this->columns[$column];
			
			if ( strpos($field, '(') === false )
				return null;
			
			$_start = strpos($field, '(') + 1;
			$length = substr($field, $_start, strpos($field, ')') - $_start );
			// Floats can has two lengths: (3,5) => 123.12345
			if ( strpos($length, ',') !== false ){
				$_n = explode(',', $length);
				$length = array_sum($_n);
			}
			return (int) $length;		
		}
	
	
	/** forgeObject
	*
	* Creates and returns an object
	*
	* @param object $db_object Row from the database with post extension data.
	* @paraam object|int $wp_object object or ID to possibly extend.
	* @return object Object instance of class $_object_class
	*/
		protected function forgeObject( &$db_object ){
			
			if ( !$db_object )
				return false;
			
			$class = $this->_object_class;
			
			return new $class( $db_object );	
		}
	
	
		public function query_by_primary_key( $pk, $select = '*' ){
			
			global $wpdb;
			
			if ( is_array($select) )
				$select = implode(', ', $select);
			
			$primary_key = $this->primary_key;
			
			$sql = "SELECT $select FROM `{$this->table}` WHERE `$primary_key` = $pk";
			
			return $wpdb->get_row( $sql );
		}
		
		public function get_primary_key_where( $where ){
			
			$sql = "SELECT {$this->primary_key} FROM `{$this->table}` WHERE ";
			
			if ( is_array($where) ){
					
				foreach($where as $col => $val){
					
					if ( empty($val) )
						throw new Exception('SQL Error: empty ' . $col . ' in ' . __FUNCTION__);
					
					$val = esc_sql($val);
					$format = $this->get_column_format($col);
					
					if ( '%s' === $format ){
						$val = like_escape( $val );
						$wheres[] = "$col LIKE '$val'";	
					}
					else{
						$wheres[] = "$col = $val";	
					}
				}
			
				$sql .= implode(" AND ", $wheres );
			}
			else {
				$sql .= trim($where);	
			}
				
			return $this->get_var( $sql );
		}
		

	/** query_by
	*
	* Queries the database using a post extension field (i.e. column)
	*
	* @param string $field The field (column) to query by
	* @param string $field_where The arguments for the field queried by
	* @param string $select The SQL "SELECT" string
	* @param array $extra_where Additional "WHERE" arguments as assoc. array.
	* @return object ??
	*/	
		public function query_by( $column, $column_where, $select = '*', $extra_where = array() ){
			
			global $wpdb;
						
			if ( !isset($this->columns[$column]) )
				throw new InvalidArgumentException('invalid field ' . $column . 'field must be a valid table column');
			
			$sql_args = array(
				$column_where,
			);
			
			if ( is_array($select) )
				$select = implode(', ', $select);
						
			$column_type = $this->get_column_format($column);
			
			$sql = "SELECT $select FROM {$this->table} WHERE $column = $column_type";
			
			if ( !empty($extra_where) ){
				
				$sql .= ' AND ';
				
				$wheres = $where_vals = array();
				
				foreach($extra_where as $col => $val){
					if ( empty($val) )
						throw new Exception('SQL Error: empty ' . $col . ' in ' . __FUNCTION__);
					$format = $this->get_column_format($col);
					$wheres[] = "$col = $format";
					$where_vals[] = $val;
				}
				
				$sql .= implode(" AND ", $wheres );
				
				$sql_args = array_merge( $sql_args, $where_vals );
			}
			
			return $wpdb->get_row( $wpdb->prepare($sql, $sql_args) );
		}
	
	
	/** query_by_multiple
	*
	* Queries the database using multiple fields (i.e. column)
	*
	* @param array $where Array of "column" => "value" args
	* @param string $select The SQL "SELECT" string
	* @return object ??
	*/	
		public function query_by_multiple(array $where, $select = '*'){
			
			global $wpdb;
						
			$sql_wheres = $sql_args = array();
			
			foreach($where as $field => $arg ){
				
				if ( empty($arg) )
					throw new Exception('SQL Error: empty "' . $field . '" in ' . __FUNCTION__);
					
				if ( !isset($this->columns[$field]) )
					throw new InvalidArgumentException('field must be a valid table column');
				
				$format = $this->get_column_format($field);
				
				$sql_wheres[] = "$field = $format";
				$sql_args[] = $arg;
			}
			
			if ( is_array($select) )
				$select = implode(', ', $select);
			
			$sql = "SELECT $select FROM {$this->table} WHERE " . implode(" AND ", $sql_wheres );
			
			return $wpdb->get_row( $wpdb->prepare($sql, $sql_args) );
		}
	
	
	public function update_var( $name, $value, array $where, $force_exists = true ){
		
		if ( !$force_exists ){
			$exists = $this->query_by_multiple( $where, $name );
			if ( $exists ) return false;
		}
		return $this->update( array($name => $value), $where );
		
	}
	
		
	/**
	* 
	* Sets the $_object_class 
	*
	* @param string $class Class name (string) of object
	*/
		public function setObjectClass($class){
			$this->_object_class = $class;
			return $this;
		}
	
	
	/** 
		---- wpdb methods ---- 
	*/
	
	/**
	 * Insert a row into a table.
	 *
	 * @see wpdb::insert()
	 */
		public function insert( $data, $format = null ){
			
			global $wpdb;
						
			$this->before_insert( $data, $format );
			
			$success = $wpdb->insert( $this->table, $data, $format );
			
			$this->after_insert($success);
			
			return $success;
		}
			
			protected function before_insert( &$data, &$format ){}
			protected function after_insert( &$success ){}
	
	
	/**
	 * Replace a row into a table.
	 *
	 * @see wpdb::replace()
	 */
		public function replace( $data, $format = null ) {
			
			global $wpdb;
						
			$this->before_replace($data, $format);
			
			$success = $wpdb->replace( $this->table, $data, $format, 'REPLACE' );
			
			$this->after_replace($success);
			
			return $success;
		}
	
			protected function before_replace( &$data, &$format ){}
			protected function after_replace( &$success ){}
	
	
	/**
	 * Update a row in the table
	 *
	 * @see wpdb::update()
	 */	
		public function update( $data, $where, $format = null, $where_format = null ){
			
			global $wpdb;
						
			$this->before_update( $data, $where, $format, $where_format );
			
			$success = $wpdb->update( $this->table, $data, $where, $format, $where_format );
			
			$this->after_update($success);
			
			return $success;
		}
			
			protected function before_update( &$data, &$where, &$format, &$where_format ){}
			protected function after_update( &$success ){}
				
	
	/**
	 * Delete a row in the table
	 *
	 * @see wpdb::delete()
	 */
		public function delete( $where, $where_format = null ) {
			
			global $wpdb;
						
			$this->before_delete($where, $where_format);
			
			$success = $wpdb->delete( $this->table, $where, $where_format );
			
			$this->after_delete($success);
			
			return $success;
		}
			
			protected function before_delete( &$where, &$where_format ){}
			protected function after_delete( &$success ){}
	
	
	/**
	 * Retrieve one row from the database.
	 *
	 * Executes a SQL query and returns the row from the SQL result via forgeObject()
	 *
	 * @see wpdb::get_row()
	 */
		public function get_row( $query = null, $output = OBJECT, $y = 0 ) {
			
			global $wpdb;
			
			$row = $wpdb->get_row( $query, $output, $y );
			
			return $this->forgeObject( $row );	
		}
	
	
	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * @see wpdb::query()
	 */
		public function query( $sql ){
			global $wpdb;
			return $wpdb->query( $sql );
		}
	
	/**
	 * Retrieve one variable from the database.
	 *
	 * @see wpdb::get_var()
	 */
		public function get_var( $query = null, $x = 0, $y = 0 ) {
			global $wpdb;
			return $wpdb->get_var( $query, $x, $y );
		}
	
	/**
	 * Retrieve one column from the database.
	 *
	 * @see wpdb::get_col()
	 */
		public function get_col( $query = null , $x = 0 ) {
			global $wpdb;
			return $wpdb->get_col( $query, $x );
		}
	
	/**
	 * Retrieve an entire SQL result set from the database (i.e., many rows)
	 *
	 * @see wpdb::get_results()
	 */
		public function get_results( $string, $output_type = OBJECT ) {
			
			global $wpdb;
			
			$results = $wpdb->get_results( $string, $output_type );
			
			if ( is_object($results) )
				return $this->forgeObject( $results );
			
			if ( is_array($results) ){
				
				$r = array();
				
				foreach($results as $result){
					if ( is_object($result) )
						$r[] =& $this->forgeObject($result);	
					else
						$r[] = $result;
				}
			}
			
			return $r;
		}
	
}

