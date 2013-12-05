<?php
class Registry {
	
	public $datatypes = array();
	
	static public $objects = array();
	
	static protected $_instance;
	
	static final function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	// can call static
	function register_datatype( $type, $args = array() ){
		
		$_this = self::instance();
		$datatype = format_class_underscore($type);
		
		$args = wp_parse_args($args, array('has_meta' => false));
		
		if ( !empty($args['extends_post_type']) ){
			
			PostExtender::instance()->register_extended_post_type( $args['extends_post_type'] );
		}
		
		if ( empty($args['model_class']) )
			$args['model_class'] = $datatype . '_Model';	
		
		if ( $args['has_meta'] && empty($args['meta_model_class']) )
			$args['meta_model_class'] = $datatype . '_Meta_Model';
		
		$_this->datatypes[ $datatype ] = $args;
		
		return $_this;
	}
		
	// can call static
	function get_object_instance( $data_type, $object_type = 'model' ){
		
		$_this = self::instance();
		$datatype = format_class_underscore($data_type);
		
		if ( !isset($_this->datatypes[ $datatype ]) )
			return null;
		
		switch($object_type){
			case 'model':
			default:
				$class = $_this->datatypes[$datatype]['model_class'];
				break;
			case 'meta_model':
				if ( !$_this->datatypes[$datatype]['has_meta'] )
					return false;
				$class = $_this->datatypes[$datatype]['meta_model_class'];
				break;
			case 'helper':
				$class = format_class_underscore($datatype . 'Helper');
				break;
		}
		
		if ( !isset(self::$objects[ $class ]) ){
			self::$objects[ $class ] = new $class();	
		}
		return self::$objects[ $class ];
	}
	
	// can call static
	function get_datatype_info( $data_type ){
		$_this = self::instance();
		return $_this->datatypes[ format_class_underscore($data_type) ];
	}
	
	/**
	* Used by function get_model()
	*/
	public function get_model( $type ){
		return $this->get_object_instance($type, 'model');	
	}
	
	/**
	* Used by function get_meta_model()
	*/
	public function get_meta_model( $type ){
		return $this->get_object_instance($type, 'meta_model');	
	}
	
	/**
	* Used by function get_helper()
	*/
	public function get_helper( $type ){
		return $this->get_object_instance($type, 'helper');	
	}
	
	/**
	* Used by function get_models()
	*/
	public function &get_objects_of_type( $object_type ){
		$objects = array();
		foreach($this->get_datatypes() as $datatype){
			if ( isset($this->datatypes[$datatype]) )
				$objects[ $datatype ] =& $this->{'get_' . $object_type}($datatype);
		}
		return $objects;	
	}
	
	public function &get_datatype_objects( $type ){
		
		$objects = array();
		$datatype = format_class_underscore($type);
		
		if ( isset($this->datatypes[ $datatype ]) ){
			
			foreach(array('model', 'meta_model', 'helper') as $obj_type){
				
				if ( isset($this->data_types[$datatype][$obj_type]) )
					
					$objects[ $datatype ] =& $this->{'get_' . $obj_type}($datatype);
			}
		}
		
		return $objects;	
	}
	
	public function get_datatypes(){
		return array_keys( $this->datatypes );	
	}
	
	public function is_registered_datatype( $type ){
		return isset($this->datatypes[ format_class_underscore($type) ]) ? true : false;	
	}
	
	// debugging
	
	function dump_objects(){
		return self::$objects;
	}
	
	function dump_datatypes(){
		return self::instance()->datatypes;
	}
			
}

function registry_dump(){
	$Registry = Registry::instance();
	return array(
		'objects' => $Registry->dump_objects(),
		'datatypes' => $Registry->dump_daatatypes()
	);
}