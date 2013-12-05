<?php
class Company_Reserves_Model extends Model {
	
	public $table_basename = 'company_reserves';
	
	public $columns = array(
		'crid'					=> "bigint(20) NOT NULL auto_increment",
		'post_id'				=> "bigint(20) NOT NULL",				// company (post-type) Post ID
		'year' 					=> "int(8) NOT NULL",
		'reserve_ids'			=> "text NOT NULL",						// Post ID's of reserve(s) in wp_reserves table
		'includes_coal'			=> "tinyint default 0",
		'mmboe'					=> "varchar(8) default 0",
		'gt_co2'				=> "varchar(8) default 0",
	);
	
	public $primary_key = 'crid';
	
	public $unique_keys = array();
	
	public $keys = array(
		'post_id' => 'post_id',
		'year' => 'year',
		'post_id__year' => 'post_id, year',
		'gt_co2' => 'gt_co2',
	);
	
	public $_object_class = 'Company_Reserves_Object';
	
	
	function get_reserve_years( $id ){
		
		$reserves = $this->get_reserves( $id );
		
		$years = array_keys($reserves);
		
		return $years;
	}
	
	function get_co2( $id ){
		
		$reserves = $this->get_reserves($id);
		ksort($reserves);
		$latest = array_pop($reserves);
		return $latest->gt_co2;
	}
	
	function get_mmboe( $id ){
		
		$reserves = $this->get_reserves($id);
		ksort($reserves);
		$latest = array_pop($reserves);
		return $latest->mmboe;
	}
	
	function get_co2_per_ej( $id ){
		
		$reserves = $this->get_reserves($id);
		ksort($reserves);
		$latest = array_pop($reserves);
		
		return $latest->get_co2_per_ej();
	}
		
	function has_reserves( $id, $year = 'all' ){
		
		$years = $this->get_reserve_years($id);
		
		if ( 'all' !== $year ){
			return in_array($year, $years);
		}
		
		return !empty( $years ) ? true : false;
	}
	
	function get_reserves( $id, $year = 'all' ){
		
		$query = "SELECT * FROM {$this->table} WHERE post_id = $id";
		
		if ( 'all' !== $year ){
			$query .= " AND year = $year";	
		}
		
		$query .= " ORDER BY year DESC";
		
		$results = $this->get_results( $query );
		
		$return = array();
		
		foreach($results as $result){
				
			$return[ $result->year ] = $result;	
		}
		
		return $return;
	}
	
}
