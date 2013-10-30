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
	* This must be set by user
	*/
	public $table_basename;
	
	
	/** $field_names 
	* 
	* The table columns
	* 
	* Array of 'column' => 'SQL string'
	* e.g. 'id' => 'bigint(20) unsigned NOT NULL auto_increment'
	*/
	public $field_names = array();
	
	
	/** $primary_key
	*
	* (required)
	*
	* @var string
	*/
	public $primary_key;
	
	
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
	* @var $db
	*
	* (object) $wpdb
	*/
	protected $db;
	
	/**
	* @var $_object_class
	*
	* String representing the object class to use.
	*/
	public $_object_class;
	
	
	/** Constructor	
	*
	* Sets up schema and wpdb
	*
	*/
		function __construct(){
			
			global $wpdb;
			
			// add table basename to $tables array
			if ( !in_array($this->table_basename, $wpdb->tables) )
				$wpdb->tables[] = $this->table_basename;
			
			// set the table name
			$this->table = $wpdb->prefix . $this->table_basename;	
			
			// add the table name to $wpdb (as property)
			if ( !isset($wpdb->{$this->table_basename}) )
				$wpdb->{$this->table_basename} = $this->table;
			
			$this->db =& $wpdb;
			
			$this->schema =& $schema;
		}
	
	
	/**
	* Returns a field's format for SQL 
	*
	* integer => %d
	* float => %f
	* string => %s (default)
	*/
		public function get_field_format($field_name){
			if ( !isset($this->field_names[$field_name]) )
				return false;
			
			$field = strtolower($this->field_names[$field_name]);
			
			if ( strpos($field, 'int') !== false )
				return '%d';
			elseif ( strpos($field, 'float') !== false )
				return '%f';
			else
				return '%s';
		}
	
	/** 
	* Returns field length max
	*
	*/
		public function get_field_length($field_name){
			if ( !isset($this->field_names[$field_name]) )
				return false;
			
			$field = $this->field_names[$field_name];
			
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
		public function query_by( $field, $field_where, $select = '*', $extra_where = array() ){
			
			if ( !isset($this->field_names[$field]) )
				throw new InvalidArgumentException('field must be a valid table column');
			
			$sql_args = array(
				$field_where,
			);
			
			if ( !is_string($select) ) {
				if ( is_array($select) )
					$select = implode(', ', $select);
			}
			
			$field_type = $this->get_field_format($field);
			
			$sql = "SELECT $select FROM `{$this->table}` WHERE `{$field}` = $field_type";
			
			if ( !empty($extra_where) ){
				
				$sql .= ' AND ';
				
				$wheres = $where_vals = array();
				
				foreach($extra_where as $col => $val){
					$_format = $this->get_field_format($col);
					$wheres[] = "`$col` = {$_format}";
					$where_vals[] = $val;
				}
				
				$sql .= implode(" AND ", $wheres );
				
				$sql_args = array_merge( $sql_args, $where_vals );
			}
			
			return $this->db->get_row( $this->db->prepare($sql, $sql_args) );
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
			
			$sql_wheres = $sql_args = array();
			
			foreach($where as $field => $arg ){
				
				if ( !isset($this->field_names[$field]) )
					throw new InvalidArgumentException('field must be a valid table column');
				
				$format = $this->get_field_format($field);
				
				$sql_wheres[] = "`$field` = {$format}";
				$sql_args[] = $arg;
			}
			
			if ( is_string($select) )
				$sel = $select;
			elseif ( is_array($select) )
				$sel = implode(', ', $select);
			
			$sql = "SELECT $sel FROM {$this->table} WHERE " . implode(" AND ", $sql_wheres );
			
			return $this->db->get_row( $this->db->prepare($sql, $sql_args) );
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
			
			$this->before_insert( $data, $format );
			
			$success = $this->db->insert( $this->table, $data, $format );
			
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
			
			$this->before_replace($data, $format);
			
			$success = $this->db->replace( $this->table, $data, $format, 'REPLACE' );
			
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
			
			$this->before_update( $data, $where, $format, $where_format );
			
			$success = $this->db->update( $this->table, $data, $where, $format, $where_format );
			
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
			
			$this->before_delete($where, $where_format);
			
			$success = $this->db->delete( $this->table, $where, $where_format );
			
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
			$result = $this->db->get_row( $query, $output, $y );
			if ( !empty($result) ){
				return $this->forgeObject($result);	
			}
			return $result;
		}
	
	
	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * @see wpdb::query()
	 */
		public function query( $sql ){
			return $this->db->query( $sql );
		}
	
	/**
	 * Retrieve one variable from the database.
	 *
	 * @see wpdb::get_var()
	 */
		public function get_var( $query = null, $x = 0, $y = 0 ) {
			return $this->db->get_var( $query, $x, $y );
		}
	
	/**
	 * Retrieve one column from the database.
	 *
	 * @see wpdb::get_col()
	 */
		public function get_col( $query = null , $x = 0 ) {
			return $this->db->get_col( $query, $x );
		}
	
	/**
	 * Retrieve an entire SQL result set from the database (i.e., many rows)
	 *
	 * @see wpdb::get_results()
	 */
		public function get_results( $string, $output_type = OBJECT ) {
			return $this->db->get_results( $string, $output_type );
		}
	
}

