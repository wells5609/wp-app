<?php

class Reserve_Model extends Model {


	public $table_basename = 'reserves';
	
	public $columns = array(
		'rid' 					=> "bigint(20) NOT NULL auto_increment",
		'post_id'				=> "bigint(20) NOT NULL",					# Post ID of company (post-type)
		'ticker'				=> "varchar(8) NOT NULL",					# Ticker of company
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

	
	protected function before_insert( &$data, &$format ){
		
		if ( isset($data['quantity']) && isset($data['resource_category']) ){
			
			$type = $data['resource_category'];
			if ( isset($data['resource_type']) )
				$type .= '.' . $data['resource_type'];
			
			$data['mmbbl_oe'] = $this->to_mmboe( $type, $data['quantity']);
			$format[] = '%f';
			
			$data['gt_co2'] = $this->to_co2( $type, $data['quantity']);
			$format[] = '%f';
			
		}
				
	}
	
	
	protected function before_update( &$data, &$where, &$format, &$where_format ){
		
		
	}
	
		
	function to_co2( $type, $quantity ){
		
		$R = ReserveHelper::instance();
		
		if ( isset($R->co2_per_unit[$type]) ){
			return $R->co2_per_unit[$type] * $quantity;
		}
		
		$ct = $R->parseCategoryType($type);
		
		if ( !empty($ct['type']) && isset($R->co2_per_unit[ $ct['type'] ]) ){
			return $R->co2_per_unit[ $ct['type'] ] * $quantity;
		}
		
		if ( isset($R->co2_per_unit[ $ct['category'] ]) ){
			return $R->co2_per_unit[ $ct['category'] ] * $quantity;
		}
	}
	
	function to_mmboe( $type, $quantity ){
		
		$R = ReserveHelper::instance();
		
		if ( isset($R->mmboe_per_unit[$type]) ){
			return $R->mmboe_per_unit[$type] * $quantity;
		}
		
		$ct = $R->parseCategoryType($type);
		
		if ( !empty($ct['type']) && isset($R->mmboe_per_unit[ $ct['type'] ]) ){
			return $R->mmboe_per_unit[ $ct['type'] ] * $quantity;
		}
		
		if ( isset($R->mmboe_per_unit[ $ct['category'] ]) ){
			return $R->mmboe_per_unit[ $ct['category'] ] * $quantity;
		}
		
	}
	
}
