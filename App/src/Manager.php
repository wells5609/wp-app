<?php

abstract class Manager {
	
	
	public $types = array();
	
	
	abstract static function instance();
	
	
	public function get_model( $type ){
		
		return Registry::get_one( '_Model', $type );	
	}
	
	
	public function &get_models(){
		
		$models = array();
		
		foreach($this->get_types() as $type)
		
			$models[$type] =& $this->get_model($type);
		
		return $models;	
	}
	
	
	public function register_type( $type ){
		
		$this->types[$type] = $type;
		
		return $this;
	}
	
	
	public function deregister_type($type){
		
		unset($this->types[$type]);
		
		return $this;	
	}
	
	
	public function get_types(){
		
		return $this->types;
	}
	
	
	public function is_registered_type( $type ){
		
		return isset($this->types[$type]);	
	}
		
}
