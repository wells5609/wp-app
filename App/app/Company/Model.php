<?php

class Company_Model extends Postx_Model {
	
	public $has_meta = true;
	
	public $table_basename = 'companies';
	
	public $field_names = array(
		'id' 					=> "bigint(20) NOT NULL",
		'ticker' 				=> "varchar(8) NOT NULL default ''",
		'cik' 					=> "varchar(32) default NULL",
		'exchange'				=> "varchar(8) default NULL",
		'sic'					=> "int(8) default NULL",
		'sector'				=> "varchar(32) default NULL",
		'industry'				=> "varchar(64) default NULL",
		'country'				=> "varchar(8) default NULL",
		'state'					=> "varchar(4) default NULL",
		'state_inc'				=> "varchar(4) default NULL",
		'full_time_employees' 	=> "int(8) default NULL",
		'cdp_score'				=> "int(4) default NULL",
		'description'			=> "text default NULL",
		'aka'					=> "text default NULL",
	);
	
	public $primary_key = 'id';
	
	public $unique_keys = array(
		'ticker'		=> 'ticker',
		'cik'		 	=> 'cik',
	);
	
	public $keys = array(
		'exchange'		=> 'exchange',
		'sic'			=> 'sic',
		'sector'		=> 'sector',
		'industry'		=> 'industry',
		'country'		=> 'country',
		'state'			=> 'state',
		'state_inc'		=> 'state_inc',
		'full_time_employees' => 'full_time_employees',
		'cdp_score'		=> 'cdp_score',
	);
	
	
	public $_object_class = 'Company_Object';
	
	
	
	function before_insert( &$data, &$format ){	
		
	}
	
}

?>