<?php

class PostExtender {

	public $post_types = array();
	
	static protected $_instance;
	
	static public $postx_objects = array();
	
	
	static final function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	
	protected function __construct(){
		
		add_action('the_post', array($this, '_extend_the_post'), 1, 1);
		
		add_action('set_object_terms', array($this, 'sync_object_terms'), 10, 6);
	}
	
	
	public function register_extended_post_type( $post_type ){
		
		$_this = self::instance();
		
		$_this->post_types[ $post_type ] = $post_type;
		
		return $_this;
	}
	
	
	public function is_extended_post_type( $post_type ){
		
		$_this = self::instance();
		
		return isset( $_this->post_types[ $post_type ] ) ? true : false;
	}
	
	
	public function get_postx( $post = null ){
		
		$_this = self::instance();
		
		if ( null !== $post ){
			
			if ( is_numeric($post) )
				$_post =& get_post($post);
			
			elseif ( $post instanceof WP_Post )
				$_post =& $post;
			
			elseif ( $post instanceof Object )
				return $post;
			
			if ( isset($_post) )
				return $_this->extend_post($_post);
		}
		
		if ( !isset($_this->_postx) && !did_action('the_post') ){
			return 'get_postx() called too early. call after the_post action.';	
		}
		
		return $_this->_postx;
	}
	
	
	public function extend_post( &$post_object, $is_the_post = false ){
	
		if ( !is_object($post_object) || !$this->is_extended_post_type( $post_object->post_type ) ){
			return;
		}
		
		$registry = Registry::instance();
		
		$this->_postx_model =& $registry->get_model( $post_object->post_type );				
		
		$_key = "{$post_object->post_type}_{$post_object->ID}";
		
		if ( !isset(self::$postx_objects[$_key]) ){
			
			$cached = cache_get( $_key, 'postx' );
			
			if ( $cached ){
			
				self::$postx_objects[$_key] =& $cached;
			}
			else {
				
				self::$postx_objects[$_key] =& $this->_postx_model->extend_post( $post_object );
			}
			
			cache_set( $_key, self::$postx_objects[$_key], 'postx' );
		}
		
		if ( !$is_the_post )
			return self::$postx_objects[$_key];
		
		$this->_postx =& self::$postx_objects[$_key];
	}
	
	
	function sync_object_terms($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids){
		
		$post = get_post($object_id);
		
		if ( !isset($post->post_type) || !$this->is_extended_post_type( $post->post_type ) )
			return;
		
		$model =& get_model( $post->post_type );
		
		if ( !$model->is_synced_taxonomy( $taxonomy ) ) 
			return;
		
		$pk = $model->primary_key;
		
		$obj = $model->query_by( $pk, $object_id );
		
		if ( empty($obj) ) 
			return;
		
		$tax_columns = $model->get_synced_tax_columns( $taxonomy );
		
		$custom_terms = array();
		
		foreach( $tax_columns as $col ){
			$custom_terms[] = $obj->$col;	
		}
		
		foreach($terms as $term){
			
			if ( is_int($term) )
				$term = get_term($term, $taxonomy)->name;
			
			if ( !in_array($term, $custom_terms) ){
				
				$model->sync_taxonomy_term( $taxonomy, $term, $object_id );
			}
		}
			
	}
	
	
	// Not a public function - callback for the_post action
	function _extend_the_post( &$post_object ){
		$_this = self::instance();
		return $_this->extend_post($post_object, true);	
	}
	
}