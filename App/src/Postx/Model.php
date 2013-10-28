<?php

class Postx_Model extends Model {
	
	
	public $_object_class = 'Postx_Object';
	
		
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

	
	public function extend_post($primary_key_value){
		
		$format = $this->schema->get_field_format($this->schema->primary_key);
		
		$sql = "SELECT * FROM `{$this->schema->table}` WHERE `{$this->schema->primary_key}` = $format";
		
		$args = array($primary_key_value);
		
		return $this->db->get_row( $this->db->prepare($sql, $args) );
	}
		
}
