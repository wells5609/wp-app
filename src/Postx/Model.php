<?php

class Postx_Model extends Model {
	
	
	public $_object_class = 'Postx_Object';
	
	public $has_meta;
	
	public $_meta_model_class = 'Meta_Model';
	
		
	/** forgeObject
	*
	* Creates and returns a PostExtensionObject
	*
	* @param object $db_object Row from the database with post extension data.
	* @paraam object|int $wp_post Post object or ID to extend.
	* @return object Postx_Object
	*/
	protected function forgeObject( &$db_object, &$wp_post = null ){
		
		if ( !$db_object )
			return false;
		
		$class = $this->_object_class;
		
		return new $class( $db_object, $wp_post );
	}

	
	public function extend_post(&$wp_post){
		
		global $wpdb;
		
		$format = $this->get_column_format($this->primary_key);
		
		$sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primary_key}` = $format";
		
		$args = array( $wp_post->ID );
		
		return $this->forgeObject( 
			$wpdb->get_row( $wpdb->prepare($sql, $args) ),
			$wp_post
		);
	
	}
	
	
	public function get_meta( $id, $key, $output = OBJECT ){
		
		if ( !$this->has_meta || !isset($this->_meta_model_class) )
			return false;
		
		$meta_model =& call_user_func(array($this->_meta_model_class, 'instance'));
		
		return $meta_model->get_object( $id, $key, $output );
	}
	
		
}
