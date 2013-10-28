<?php

class ManagerRegistry implements RegistryInterface {
	
	static $managers = array();
	
	static private $_instance;
		
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	function get($name = null){
		
		if ( null === $name ){
			return self::$managers;
		}
		else if ( !isset(self::$managers[$name]) ){

			$_this = self::instance();
		
			self::$managers[$name] = $_this->classFromName($name);
		}
		
		return self::$managers[$name];
	}
	
	function classFromName($name){
		$class = ucfirst($name);
		if ( strpos($class, 'Manager') === false ){
			$class .= 'Manager';	
		}
		return $class;
	}
	
	// Extra methods
	
	/**
	* Registers a Manager
	*
	* Can pass a class to override default convention.
	* Can also pass path to automatically require the file.
	*/
	function register($name, $class = null, $path = null){
		
		if ( null === $class )
			$class = $this->classFromName($name);
		
		self::$managers[$name] = $class;
		
		if ( null !== $path )
			require_once $path;
		
		return $this;
	}
	
	// deregisters a method
	function deregister($name){
		unset(self::$managers[$name]);
		return self::instance();
	}
	
	// returns all managers
	function get_all(){
		return self::$managers;	
	}
	
	// retursn all schemas
	function get_all_schemas(){
		
		$schemas = array();
		foreach($this->get_all() as $name => $class){
			$schemas[$name] =& get_instance($class)->get_schemas();	
		}
		
		return $schemas;
	}
	
		
}
