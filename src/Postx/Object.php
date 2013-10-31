<?php

class Postx_Object extends Object {
	
	public $post;
	
	public $the_post = false;
	
	/**
	* Overwriting Object constructor
	* Sets properties from db result, adds $post using param or global
	*/
	function __construct( &$db_object, &$wp_post = null ){
		
		$this->import($db_object);
		
		if ( null === $wp_post )
			$this->importPost($GLOBALS['post']);
		else 
			$this->importPost($wp_post);
	}
	
	function __get( $var ){
		if ( strpos($var, 'post_') === 0 ){
			return isset($this->post->$var) ? $this->post->$var : NULL;	
		}
		return isset($this->$var) ? $this->$var : NULL;	
	}
	
	// Imports WP_Post into Object
	protected function importPost(&$post){
		
		if ( is_object($post) ) {
			
			if ( $post->ID == $this->id )
				$this->the_post = true;
			
			$this->post =& $post;
		}
		else if ( is_numeric($post) ){
			
			$this->post =& get_post($post, OBJECT);
		}
	
	}
	
	
	function __sleep(){
		$this->the_post = false;	
	}
	function __destruct(){
		$this->the_post = false;	
	}
	
}
