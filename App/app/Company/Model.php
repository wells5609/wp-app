<?php

class Company_Model extends Postx_Model {
		
	protected $before_insert = array(
	//	'this.before_insert',
	);
	
	protected $after_insert = array(
	//	'this.new_company',
	);
	
	
	function before_insert( &$data ){	
	}
	
	function new_company( &$db_object ){
	}
	
}

?>