<?php

class Company_Reserves_Object extends Object {
	
	const PRECISION = 4;
	
	protected function onImport(){
		
		$id = $this->post_id;
		
		if ( null === $id ){
			$post =& get_postx();
			$id = $post->id;	
		}
		
		$reserve_model =& get_model('reserve');
		
		$query =  "SELECT * FROM {$reserve_model->table} WHERE post_id = $id";
		
		if ( !empty($this->year) ){
			$query .= " AND year = {$this->year}";
		}
		
		$query .= " ORDER BY year DESC";
		
		$reserves = $reserve_model->get_results( $query );
		
		$return = array();
		
		// add keys to ease traversing
		foreach($reserves as &$object){
			
			$return[ $object->type ] =& $object;
		}
		
		krsort($return);
		
		$this->reserves = $return;
	}
	
	
	function get_type_labels(){
		
		$types = array();
		$helper = get_helper('reserve');
		
		foreach($this->reserves as $type => &$obj){
			$types[$type] = $helper->get_label($type);
		}
		return $types;
	}
	
	function get_years(){
		$years = array();
		foreach($this->reserves as $type => &$obj){	
			$years[$type] = $obj->year;	
		}
		return $years;
	}
	
	function get_mmboe(){
		$mmboe = array();
		foreach($this->reserves as $type => &$obj){	
			$mmboe[$type] = $obj->mmboe;	
		}
		return array_sum( $mmboe );
	}
	
	function get_gtco2(){
		$co2 = array();
		foreach($this->reserves as $type => &$obj){	
			$co2[$type] = $obj->gt_co2;	
		}
		return array_sum( $co2 );
	}
	
	function get_ej(){
		$ej = 0;
		foreach($this->reserves as $type => &$obj){	
			$ej += $obj->energy_equivalent;	
		}
		return round( $ej, self::PRECISION );
	}
	
	function get_co2_per_mmboe(){
		$co2 = $this->get_gtco2();
		$mmboe = $this->get_mmboe();
		return round( ($co2*1000)/$mmboe, self::PRECISION );	
	}
	
	function get_co2_per_ej(){
		$co2 = $this->get_gtco2();
		$energy = $this->get_ej();
		return round( ($co2*1000000000)/$energy, self::PRECISION );
	}
	
	
	function __wakeup(){
	}
	function __sleep(){
		return array_keys( get_object_vars($this) );	
	}
	
}
