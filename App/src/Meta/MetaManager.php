<?php
	
class MetaManager extends Manager {
	
	static private $_instance;
	
	public $types = array();
	
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	
	// Overwriting method to use different string
	public function get_model( $type ){
		return Registry::get_one( '_Meta_Model', $type );	
	}
	
	
}
