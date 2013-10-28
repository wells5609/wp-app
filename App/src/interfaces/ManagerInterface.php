<?php

interface ManagerInterface {
	
	function register_type( $type );
	
	function deregister_type( $type );
	
	function get_types();
	
	function get_model( $type );
	
	function get_schema( $type );
	
	function get_schemas();
	
}
