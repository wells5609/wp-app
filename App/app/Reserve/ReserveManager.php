<?php
	
class ReserveManager implements ManagerInterface {
	
	static private $_instance;
	
	public $types = array();
	
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
		
	public function register_type( $type ){
		$this->types[ $type ] = $type;
		return $this;
	}
	
	public function deregister_type( $type ){
		unset($this->types[$type]);
		return $this;	
	}
	
	public function get_types(){
		return $this->types;
	}
			
	public function get_model( $type ){
		return ModelRegistry::get( $type . '_Model' );	
	}
		
	public function get_schema( $type ){
		return $this->get_model($type)->schema;	
	}
	
	public function get_schemas(){
		static $schemas = array();
		foreach($this->types as $type){
			$model =& $this->get_model($type);
			$schemas[ $model->schema->table_basename ] =& $model->schema;	
		}
		return $schemas;
	}
	
}

