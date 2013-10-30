<?php

class Company_Reserves_Model extends Model {
	
	public $table_basename = 'company_reserves';
	
	public $field_names = array(
		'crid' 					=> "bigint(20) NOT NULL auto_increment",
		'post_id'				=> "bigint(20) NOT NULL",
		'ticker' 				=> "varchar(8) NOT NULL",
		'year' 					=> "int(8) NOT NULL",
		'classification'		=> "varchar(16) NOT NULL",
		'includes_coal'			=> "tinyint default 0",
		'gt_co2'				=> "float(4,4) default 0",
	);
	
	public $primary_key = 'crid';
	
	public $unique_keys = array(
		'post_id_year' => 'post_id, year',
		'ticker_year' => 'ticker, year',
	);
	
	public $keys = array(
		'post_id' => 'post_id',
		'ticker' => 'ticker',
		'year' => 'year',
	);
	
}
