<?php

class Company_Meta_Model extends Meta_Model {
	
	public $table_basename = 'company_meta';
	
	public $field_names = array(
		'meta_id'			=> "bigint(20) unsigned NOT NULL auto_increment",
		'post_id' 			=> "bigint(20) NOT NULL",
		'ticker'			=> "varchar(8) NOT NULL",
		'meta_key'			=> "varchar(255) NOT NULL",
		'meta_value'		=> "longtext default NULL",
		'is_updated'		=> "tinyint default 0",
		'time_updated'		=> "timestamp default 0",
		'update_interval'	=> "int(8) default 0",
	);
	
	public $primary_key = 'meta_id';
	
	public $unique_keys = array(
	);
	
	public $keys = array(
		'post_id'			=> 'post_id',
		'ticker'			=> 'ticker',
		'meta_key'			=> 'meta_key',
		'post_id_meta_key' 	=> 'post_id, meta_key',
		'ticker_meta_key' 	=> 'ticker, meta_key',
	);
	
		
	/**
	* Column in the meta table which maps to a unique object identifier.
	*/
	public $id_column = 'post_id';
	
	
	public $_object_class = 'Company_Meta_Object';
	
	
}