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
	
	public function update_meta( $data ){
		
		$exists = $this->get_value($data[$this->id_column], $data['meta_key']);
		$defaults = array(
			'time_updated'		=> time(),
			'update_interval'	=> DAY_IN_SECONDS/2,
		);
		$args = array_merge($defaults, $data);
		
		if ( !$exists ){
			return $this->insert($args);
		}
		else{
			return $this->update( 
				array(
					'meta_value' => (string) $args['meta_value'], 
					'time_updated' => $args['time_updated'], 
					'update_interval' => $args['update_interval']
				), 
				array(
					$this->id_column => $args[$this->id_column], 
					'meta_key' => $args['meta_key']
				)
			);
		}
	}
	
	public function get_value( $object_id, $meta_key = null, $id_column = null){
		return $this->get_object($object_id, $meta_key, VALUE, $id_column);	
	}
	
	public function get_object( $object_id, $meta_key = null, $output = OBJECT, $id_column = null ){
		
		if ( null === $id_column ){
			if ( !isset($this->id_column) )
				throw new Exception('trying to call "' . __CLASS__ . '::get_object()" without "id_column"');
			$id_column = $this->id_column;
		}
		
		$select = '*';
		
		if ( null === $meta_key ){
			$db_object =& $this->query_by( $id_column, $object_id );
		}
		else {
			if ( VALUE == $output )
				$select = 'meta_value';
			
			$db_object =& $this->query_by_multiple( array( $id_column => $object_id, 'meta_key' => $meta_key, ), $select );
		}
		
		if ( empty($db_object) )
			return false;
		
		$meta_object =& $this->forgeObject( $db_object );
		
		if ( OBJECT !== $output ){
			
			switch ($output){
				
				case VALUE:
				case 'val':
					$meta_object = $meta_object->meta_value;
					break;
				
				case ARRAY_A:
				case 'arr':
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
