<?php

class Company_Reserves_Model extends Model {
	
	public $table_basename = 'company_reserves';
	
	public $columns = array(
		'crid' 					=> "bigint(20) NOT NULL auto_increment",
		'post_id'				=> "bigint(20) NOT NULL",				// company (post-type) Post ID
		'ticker' 				=> "varchar(8) NOT NULL",				// company ticker
		'year' 					=> "int(8) NOT NULL",
		'reserve_ids'			=> "text NOT NULL",						// Post ID's of reserve(s) (Post-Type)
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
	
	protected function before_insert( &$data, &$format ){
		
	}
	
	protected function before_update( &$data, &$where, &$format, &$where_format ){
		
	}
						
	
}
