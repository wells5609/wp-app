<?php
	
class ReserveManager extends Manager {
	
	static private $_instance;
	
	public $types = array();
	
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
		
}
