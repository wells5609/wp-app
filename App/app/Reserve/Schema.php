<?php

class Reserve_Schema extends Schema {
	
	public $table_basename = 'reserves';
	
	public $field_names = array(
		'rid' 					=> "bigint(20) NOT NULL auto_increment",
		'post_id'				=> "bigint(20) NOT NULL",		# post_id of post-type reserve
		'ticker'				=> "varchar(8) NOT NULL",		# ticker of post-type company
		'year' 					=> "int(8) NOT NULL",
		'classification'		=> "varchar(16) NOT NULL",
		'resource_category'		=> "varchar(16) NOT NULL",
		'resource_type'			=> "varchar(16) NOT NULL",
		'quantity'				=> "float(8,4) default 0",
		'unit'					=> "varchar(8) default 'mmbbl'",
		'mmbbl_oe'				=> "float(8,4) default 0",
		'gt_co2'				=> "float(4,4) default 0",
	);
	
	public $primary_key = 'rid';
	
	public $unique_keys = array(
	);
	
	public $keys = array(
		'post_id' => 'post_id',
		'ticker' => 'ticker',
		'year' => 'year',
		'classification' => 'classification',
	);

}
