<?php
abstract class Model {
	
	/**
	* @var $schema
	*
	* Schema object
	*/
	public $schema;
	
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
	
	
	/** DB Action Callbacks */
	
	// Callables to run before a row is inserted
	protected $before_insert = array();
	
	// Callables to run before a row is updated
	protected $before_update = array();
	
	// Callables (values) to run before fields (keys) are updated
	protected $before_update_field = array();
	
	// Callables to run after a row is inserted
	protected $after_insert = array();
	
	// Callables to run after a row is updated
	protected $after_update = array();
	
	// Callables (values) to run after fields (keys) are updated
	protected $after_update_field = array();
	
	// Callables to run before a row is deleted
	protected $before_delete = array();
	
	// Callables to run after a row is deleted
	protected $after_delete = array();
	
	
	/** Constructor	
	*
	* Sets up schema and wpdb
	*
	* @param object $schema Schema object to use
	*/
		function __construct( Schema &$schema ){
			
			global $wpdb;
			
			$this->db =& $wpdb;
			
			$this->schema =& $schema;
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
			
			if ( !isset($this->schema->field_names[$field]) )
				throw new InvalidArgumentException('field must be a valid table column');
			
			$sql_args = array(
				$field_where,
			);
			
			if ( is_string($select) )
				$select_string = $select;
			elseif ( is_array($select) )
				$select_string = implode(', ', $select);
			
			$field_type = $this->schema->get_field_format($field);
			
			$sql = "SELECT $select_string FROM `{$this->schema->table}` WHERE `{$field}` = $field_type";
			
			if ( !empty($extra_where) ){
				
				$sql .= ' AND ';
				
				$wheres = $where_vals = array();
				
				foreach($extra_where as $col => $val){
					$_format = $this->schema->get_field_format($col);
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
				
				if ( !isset($this->schema->field_names[$field]) )
					throw new InvalidArgumentException('field must be a valid table column');
				
				$format = $this->schema->get_field_format($field);
				
				$sql_wheres[] = "`$field` = {$format}";
				$sql_args[] = $arg;
			}
			
			if ( is_string($select) )
				$selectStr = $select;
			elseif ( is_array($select) )
				$selectStr = implode(', ', $select);
			
			$sql = "SELECT $selectStr FROM {$this->schema->table} WHERE " . implode(" AND ", $sql_wheres );
			
			return $this->db->get_row( $this->db->prepare($sql, $sql_args) );
		}
	
		
	/**
	* 
	* Sets the $_object_class 
	*
	* @param string $class Class name (string) of object
	* @return void
	*/
		public function setObjectClass($class){
			$this->_object_class = $class;	
		}
	
	
	/** 
		---- wpdb methods ---- 
	*/
	
	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * @see wpdb::query()
	 */
		public function query( $sql ){
			return $this->db->query( $sql );
		}
	
	function doAction( $type, $data = array() ){
		
		if ( strpos($type, 'before_') !== false && !empty($this->$type) ){
			foreach($this->$type as $idx => $func){
				if ( strpos($func, 'this.') !== false ){
					$func = array($this, str_replace('this.', '', $func));	
				}
				if ( strpos($type, '_field') !== false ){
					if ( is_callable($func) && isset($data[$idx]) ){
						call_user_func_array($func, &$data);
					}
				}
				else {
					if ( is_callable($func) )
						call_user_func_array($func, &$data);
				}
			}	
		}
		
		elseif ( strpos($type, 'after_') !== false && !empty($this->$type) ){
			foreach($this->$type as $func){
				if ( strpos($func, 'this.') !== false ){
					$func = array($this, str_replace('this.', '', $func));	
				}
				if ( is_callable($func) ){
					call_user_func($func, &$data);
				}
			}	
		}	
		
	}
	
	/**
	 * Insert a row into a table.
	 *
	 * @see wpdb::insert()
	 */
		public function insert( $data, $format = null ){
			
			$this->doAction( 'before_insert', array( 'data' => &$data ) );
			
			$success = $this->db->insert( $this->schema->table, $data, $format );
			
			$this->doAction( 'after_insert', $success );
			
			return $success;
		}
	
	/**
	 * Replace a row into a table.
	 *
	 * @see wpdb::replace()
	 */
		public function replace( $data, $format = null ) {
			return $this->db->replace( $this->schema->table, $data, $format, 'REPLACE' );
		}
	
	/**
	 * Update a row in the table
	 *
	 * @see wpdb::update()
	 */	
		public function update( $data, $where, $format = null, $where_format = null ){
			
			$this->doAction('before_update', array('data' => &$data, 'where' => &$where, 'format' => &$format, 'where_format' => &$where_format));
			
			$this->doAction('before_update_field', array('data' => &$data, 'where' => &$where, 'format' => &$format, 'where_format' => &$where_format));
			
			$success = $this->db->update( $this->schema->table, $data, $where, $format, $where_format );
			
			$this->doAction('after_update', &$success);
			
			$this->doAction('after_field_update', &$success);
			
			return $success;
		}
	
	/**
	 * Delete a row in the table
	 *
	 * @see wpdb::delete()
	 */
		public function delete( $where, $where_format = null ) {
			
			$this->doAction( 'before_delete', array( 'where' => &$where, 'where_format' => &$where_format ) );
			
			$success = $this->db->delete( $this->schema->table, $where, $where_format );
			
			$this->doAction( 'after_delete', &$success);
			
			return $success;
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
	 * Retrieve one row from the database.
	 *
	 * Executes a SQL query and returns the row from the SQL result.
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

