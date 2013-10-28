<?php

class Meta_Model extends Model {
	
	/**
	* Column in the meta table which maps to a unique object identifier.
	*
	* For example:
	* in wp_meta, this would be "post_id"
	* in wp_users, this would be "user_id"
	*/
	public $id_column;
	
	public $_object_class = 'Meta_Object';
	
	
	public function get_value( $id_col_value, $meta_key = null, $id_column = null){
		return $this->get_object($id_col_value, $meta_key, VALUE, $id_column);	
	}
	
	public function get_object( $id_col_value, $meta_key = null, $output = OBJECT, $id_column = null ){
		
		if ( null === $id_column ){
			if ( !isset($this->id_column) )
				throw new Exception('trying to call "get_xmeta" without "id_column"');
			$id_column = $this->id_column;
		}
		
		if ( null === $meta_key ){
			$db_object =& $this->query_by( $id_column, $id_col_value );
		}
		else {
			$db_object =& $this->query_by_multiple( 
				array(
					$id_column => $id_col_value,
					'meta_key' => $meta_key,
				) 
			);
		}
		
		if ( !isset($db_object) || empty($db_object) )
			return false;
		
		$meta_object =& $this->forgeObject( $db_object );
		
		if ( OBJECT !== $output ){
			
			switch ($output){
				
				case VALUE:
				case 'value':
					$meta_object = $meta_object->meta_value;
					break;
				
				case ARRAY_A:
				case 'array':
					$meta_object = (array) $meta_object;
					break;
					
				case ARRAY_N:
					$meta_object = array_values( (array) $meta_object );
					break;				
			}
		}
		
		return $meta_object;
	}
		
}
