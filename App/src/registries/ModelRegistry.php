<?php

class ModelRegistry implements RegistryInterface {
	
	static private $models = array();
	
	static $model_paths = array();
	
	static private $_instance;
		
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
		
	function get($name = null){
		
		if ( null === $name ){
			return self::$models;
		}
		else if ( !isset(self::$models[$name]) ){
			
			$_this = self::instance();
		
			$class = $_this->classFromName($name);
			
			self::$models[$name] = new $class( SchemaRegistry::get($name) );
		}
			
		return self::$models[$name];
	}
	
	
	function classFromName( $name ){
		
		$class = ucwords(str_replace('-', ' ', $name));
		$class = trim(str_replace(' ', '', $class));
		
		if ( strpos($class, '_Model') === false )
			$class .= '_Model';	
		
		return $class;
	}
	
}


?>