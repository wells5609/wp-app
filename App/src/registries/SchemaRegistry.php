<?php

class SchemaRegistry implements RegistryInterface {
	
	static private $schemas = array();
		
	static private $_instance;
		
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	// gets a schema instance
	function get($name = null){
		
		if ( null === $name ){
			return self::$schemas;
		}
		else if ( !isset(self::$schemas[$name]) ){
			
			$_this = self::instance();
		
			$class = $_this->classFromName($name);
			
			self::$schemas[$name] = new $class();
		}
		
		return self::$schemas[$name];
	}
	
			
	function classFromName( $name ){
		// remove _Model from name 
		$class = str_replace('_Model', '', $name);
		// convert '-' to ' ', capitalize first letter of each word, remove all ' '
		$class = trim(str_replace(' ', '', ucwords(str_replace('-', ' ', $class))));
		// add '_Schema' to end
		if ( strpos($class, '_Schema') === false )
			$class .= '_Schema';
		return $class;
	}
	
}

