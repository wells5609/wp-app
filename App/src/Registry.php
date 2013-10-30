<?php

class Registry {
	
	static public $data = array();
	
	static private $_instance;
		
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	/**
	* Registers a Manager
	*/
	function register($component, $type, &$object){
		
		if ( !isset(self::$data[$type][$component]) )
			self::$data[$type][$component] =& $object;
		
		return self::instance();
	}
	
	// deregisters data
	function deregister( $component, $type ){
		unset(self::$data[$type][$component]);
		return self::instance();
	}
	
	// gets one data
	function get_one( $component, $type ){
		
		if ( !isset(self::$data[ $type ][ $component ]) ){
		
			$class = self::classFromName($component, $type);
		
			self::$data[$type][$component] = new $class();
		}
		
		return self::$data[$type][$component];
	}
	
	// returns all datas
	function get_all(){
		return self::$data;	
	}
	
	function classFromName( $component, $name ){
		
		$class = ucwords(str_replace('-', ' ', $name));
		$class = trim(str_replace(' ', '', $class));
		
		if ( strpos($class, $component) === false )
			$class .= $component;	
		
		return $class;
	}
	
	
}
