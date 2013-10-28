<?php

abstract class Schema {
	
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
	* Array of 'column' => 'table setup SQL str'
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
	* @var $has_meta
	*
	* Whether this object has a meta table.
	*/
	public $has_meta = false;
	
	
	/**
	* Setup Schema and make known to $wpdb
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
	
	public function get_field_length($field_name){
		
		if ( !isset($this->field_names[$field_name]) )
			return false;
		
		$field = $this->field_names[$field_name];
		
		if ( strpos($field, '(') === false ){
			return null;
		}
		
		$_start = strpos($field, '(') + 1;
		$_end = strpos($field, ')');
		$length = substr($field, $_start, $_end - $_start );
		
		// Floats can two length arguments: the first is the limit before
		// the decimal, the second is the limit after the decimal.
		// e.g. float(2,3) would mean "34.25555" is saved as "34.256" and
		// this function would return "5"
		if ( strpos($length, ',') !== false ){
			$_n = explode(',', $length);
			$length = array_sum($_n);
		}
		
		return (int) $length;		
	}
		
}
