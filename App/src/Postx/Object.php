<?php

class Postx_Object extends Object {
	
	public $post;
	
	/**
	* Sets up object properties from db result and uses either
	* $wp_post param or global $post to set post property.
	*/
	function __construct( &$db_object, &$wp_post = null ){
		
		global $post;
		
		foreach($db_object as $key => $val){
			$this->$key = $val;	
		}
		
		if ( null !== $wp_post ){
			$this->setPost($wp_post);
		}
		elseif ( $post->ID == $this->id ){
			$this->setPost($post);
		}
		else {
			$this->setPost($post->ID);
		}
		
	}
	
	function __get( $var ){
		if ( strpos($var, 'post_') === 0 ){
			return isset($this->post->$var) ? $this->post->$var : NULL;	
		}
		return isset($this->$var) ? $this->$var : NULL;	
	}
	
	// Sets the post using an ID or object
	protected function setPost(&$post){
		
		if ( is_object($post) ){
			$this->post =& $post;
		}
		elseif ( is_numeric($post) ){
			$this->post =& get_post($post, OBJECT);
		}
			
	}
	
	
	/** Post field getters */
	
	public function get_post_field( $name ){
		return isset($this->post->$name) ? $this->post->$name : NULL;	
	}
		
}
