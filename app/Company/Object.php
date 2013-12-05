<?php

class Company_Object extends Postx_Object {
	
	function onImport(){
		
		$this->full_time_employees = number_format( $this->full_time_employees, 0 );	
		
		$this->marketcap = number_format( $this->marketcap, 2 );	
	
	}
	
	function __wakeup(){
		
	}
	function __sleep(){
		return array_keys( get_object_vars($this) );	
	}
		
}