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
	
	
	// gets one data
	function get_one( $component, $type ){
		
		if ( !isset(self::$data[ $type ][ $component ]) ){
			
			$_this = self::instance();
			
			$class = $_this->classFromName($component, $type);
		
			self::$data[$type][$component] = new $class();
		}
		
		return self::$data[$type][$component];
	}
	
	function get_all_for_type( $type ){
		
		$r = array();
		
		if ( isset(self::$data[$type]) ){
			$r = self::$data[$type];	
		}
		
		return $r;
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
	
	
	/** Managers, their Models, their Schemas */
	
	/**
	* Registers a data
	*/
	function register_manager( $name, &$object = null ){
		
		$_this = self::instance();
		
		if ( class_exists('AppApi') ){
			// Add feature support
			$_name = strtolower($name);
			
			if ( 'postx' === $_name )
				AppApi::instance()->add_support('postx');		
			elseif ( 'meta' === $_name )
				AppApi::instance()->add_support('meta');
		}
		
		if ( !isset(self::$data['Manager'][$name]) ){
			
			if ( null !== $object )
				self::$data['Manager'][$name] =& $object;
			
			else
				self::$data['Manager'][$name] =& $_this->get_manager($name);
		}
		
		return $_this;
	}
	
	// deregisters a Manager
	function deregister_manager( $name ){
		unset(self::$data['Manager'][$name]);
		return self::instance();
	}
	
	// gets a Manager
	function get_manager( $name ){
		
		if ( isset(self::$data['Manager'][$name]) )
			return self::$data['Manager'][$name];
		
		$manager = self::instance()->classFromName('Manager', $name);
		
		if ( !class_exists($manager) ){
			$manager = 'Manager'; // use default class 'Manager'
		}
		
		self::$data['Manager'][$name] =& call_user_func( array($manager, 'instance') );
		
		return self::$data['Manager'][$name];
	}
	
	function get_managers(){
		
		return self::$data['Manager'];
	}
	
}