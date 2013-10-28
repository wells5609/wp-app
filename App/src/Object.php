<?php

abstract class Object {
	
	function __construct( &$db_object ){
		
		foreach((array)$db_object as $key => $val){
			$this->$key = $val;	
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
			
}
