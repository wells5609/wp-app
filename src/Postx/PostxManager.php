<?php

class PostxManager extends Manager {

	static private $_instance;
	
	static public $postxs = array();
	
	public $_postx_model;
	
	public $_postx;
	
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	private function __construct(){
		add_action('the_post', array($this, 'extend_the_post'), 1, 1);
	}
	
	public function get_postx( $post = null ){
		
		if ( null !== $post ){
			if ( is_a($post, 'WP_Post') )
				$_post =& $post;
			elseif ( is_numeric($post) )
				$_post =& get_post($post);
			else
				return false;
			
			return $this->extend($_post);
		}
		
		if ( !isset($this->_postx) && !did_action('the_post') ){
			return 'get_postx() called too early. call after the_post action.';	
		}
		
		return $this->_postx;
	}
	
	
	// Do not call directly - callback for 'the_post' action
	public function extend_the_post( &$post_object ){
		
		return $this->extend($post_object, true);
	}
	
	protected function extend( &$post_object, $is_the_post = false ){
		
		if ( $this->is_registered_type( $post_object->post_type ) ){
		
			$this->_postx_model =& $this->get_model( $post_object->post_type );				
			
			$_key = $post_object->post_type . '_' . $post_object->ID;
			
			if ( !isset(self::$postxs[$_key]) ){
				
				if ( $cached = wp_cache_get( $_key, 'postxs' ) )
					self::$postxs[$_key] =& $cached;
				
				else {
					self::$postxs[$_key] =& $this->_postx_model->extend_post( $post_object );
					wp_cache_add( $_key, self::$postxs[$_key], 'postxs' );
				}
			}
			
			if ( $is_the_post )
				$this->_postx =& self::$postxs[$_key];
			else
				return self::$postxs[$_key];
		}
			
	}
	
}
