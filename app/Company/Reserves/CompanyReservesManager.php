<?php
	
class CompanyReservesManager extends Manager {
	
	static private $_instance;
	
	public $types = array();
	
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	
	public function get_model( $type ){
		
		return Registry::get_one( '_Model', $type );	
	}
	
	
		
}
