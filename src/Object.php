<?php
class Object {
	
	public function __construct( &$db_object ){
		
		$this->import($db_object);
	}
	
	// DO NOT make private... not again.
	protected function import( &$vars ){
		
		foreach((array) $vars as $key => $val){
			$this->$key = $val;	
		}
		
		$this->onImport();
	}
	
	
	protected function onImport(){}
	
	
	/**
	* Magically handle getters and setters.
	*
	* @param string $function
	* @param array $arguments
	* @return mixed
	*/
	public function __call($function, $arguments){
		
		// Getters following the pattern 'get_{$property}'
		if ( 0 === strpos($function, 'get_') ) {
			
			$property = substr($function, 4);
			
			if ( isset($this->$property) )
				return $this->{$property};
		}
		
		// Setters following the pattern 'set_{$property}'
		elseif ( 0 === strpos($function, 'set_') ) {
			
			$property = substr($function, 4);
			
			$this->{$property} = $arguments[0];
		}
		
		// Echoers following the pattern 'the_{$property}'
		elseif ( 0 === strpos($function, 'the_') ) {
			
			$property = substr($function, 4);
			
			if ( isset($this->$property) )
				echo $this->{$property};
		}
		
	}
		
			
	function __get( $var ){
		return isset($this->$var) ? $this->$var : NULL;	
	}
	
	function __set( $var, $value ){
		$this->$var = $value;	
	}
	
	function __isset( $var ){
		return isset($this->$var);	
	}
	
	function __wakeup(){}
	
	function __sleep(){
		return array_keys( get_object_vars($this) );	
	}
}