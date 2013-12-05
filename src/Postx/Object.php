<?php

class Postx_Object extends Object {
	
	public $post;
	
	public $is_the_post = false;
	
	/**
	* Overwriting Object constructor
	* Sets properties from db result, adds $post using param or global
	*/
	function __construct( &$db_object, &$wp_post = null ){
		
		$this->import($db_object);
		
		if ( null === $wp_post ){
			if ( $this->is_the_post )
				$this->importPost($GLOBALS['post']);
			else
				$this->importPost($this->id); // change this if not using 'id'
		}
		else 
			$this->importPost($wp_post);
	}
	
	function __get( $var ){
		if ( strpos($var, 'post_') === 0 ){
			return isset($this->post->$var) ? $this->post->$var : NULL;	
		}
		return isset($this->$var) ? $this->$var : NULL;	
	}
	
	public function append_data($name, &$data){
		$this->$name = $data;	
	}
	
	// Imports WP_Post into Object
	protected function importPost(&$post){
		
		if ( is_object($post) ) {
			
			if ( $post->ID == $this->id )
				$this->is_the_post = true;
			
			$this->post =& $post;
		}
		else if ( is_numeric($post) ){
			
			$this->post =& get_post($post, OBJECT);
		}
	
	}
	
	public function get_metadata( $output = OBJECT ){
		
		$meta_model = get_meta_model( $this->post->post_type );
		
		$results = $meta_model->get_results( "SELECT * FROM {$meta_model->table} WHERE {$meta_model->id_column} = {$this->id}" );	
		
		if ( OBJECT === $output )
			return $results;
		
		switch($output){
			case ARRAY_A:
				return (array) $results;
			case ARRAY_N:
				return array_values( (array) $results );
			case VALUE:	
				return wp_list_pluck($results, 'meta_value');
		}
	
	}
		
	
	function __wakeup(){
	}
	
	function __sleep(){
		return array_keys( get_object_vars( $this ) );
	}
			
}
