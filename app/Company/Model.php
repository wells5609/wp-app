<?php

class Company_Model extends Postx_Model {
	
	public $table_basename = 'companies';
	
	public $columns = array(
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
		'marketcap'				=> "decimal(6,2) default 0",
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
	
	public $has_meta = true;
	
	public $_meta_model_class = 'Company_Meta_Model';
	
	// associative array of 'taxonomy' => array('table_column', ...) to sync
	public $synced_taxonomies = array(
		'industry' => array('sector', 'industry'),
		'sic' => array('sic'),
		'exchange' => array('exchange'),
	);
	
	
	public function sync_taxonomy_term( $taxonomy, $term, $object_id ){
				
		switch( $taxonomy ){
			
			case 'industry':
				
				$term_object = get_term_by('name', $term, $taxonomy);
				
				if ( 0 == $term_object->parent )
					$this->update( array('sector' => $term_object->name), array('id' => $object_id) );
				else
					$this->update( array('industry' => $term_object->name), array('id' => $object_id) );
				break;	
				
			case 'sic':
				
				$this->update( array('sic' => $term), array('id' => $object_id) );
				break;
				
			case 'exchange':
				
				$this->update( array('sic' => $term), array('id' => $object_id) );
				break;
			
			default: break;
		}
	
	}
	
	
}

?>