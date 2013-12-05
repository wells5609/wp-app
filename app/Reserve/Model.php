<?php

class Reserve_Model extends Model {

	public $table_basename = 'reserves';
	
	public $columns = array(
		'rid' 				=> "bigint(20) NOT NULL auto_increment",
		'post_id'			=> "bigint(20) NOT NULL",					# Post ID of company (post-type)
		'year' 				=> "int(4) NOT NULL",
		'type'				=> "varchar(32) NOT NULL",
		'unit'				=> "varchar(8) default 'mmbbl'",
		'classification'	=> "varchar(16) default 'P1'",
		'quantity'			=> "int(8) default 0",
		'mmboe'				=> "varchar(8) default 0",
		'gt_co2'			=> "varchar(8) default 0",
	);
	
	public $primary_key = 'rid';
	
	public $unique_keys = array();
	
	public $keys = array(
		'post_id'			=> 'post_id',
		'year'				=> 'year',
		'type'				=> 'type',
		'classification'	=> 'classification',
	);
	
	public $_object_class = 'Reserve_Object';
	
	
	protected function before_insert( &$data, &$format ){
		
		if ( isset($data['quantity']) && isset($data['type']) ){
			
			if ( !isset($data['mmboe']) ){
				$data['mmboe'] = $this->to_mmboe( $data['type'], $data['quantity']);
			}
			
			if ( !isset($data['gt_co2']) ){
				$data['gt_co2'] = $this->to_co2( $data['type'], $data['quantity']);
			}
		}
				
	}
	
	function energy_equivalent( $type, $quantity ){
		
		$helper = get_helper('reserve');
		
		return $helper->convert_to_energy_equivalent($type, $quantity);
			
	}
		
	function to_co2( $type, $quantity ){
		
		$R = get_helper('reserve');
		
		return $R->convert_to_co2($type, $quantity);
	}
	
	function to_mmboe( $type, $quantity ){
		
		$R = get_helper('reserve');
		
		return $R->convert_to_mmboe($type, $quantity);
	}
	
}
