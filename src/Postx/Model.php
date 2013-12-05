<?php
/**
* The Postx_Model is used to "extend" the WP_Post object using
* data from another table. 
*
* Each postx table corresponds to a custom post-type, and each 
* row in that table corresponds to a single post (via the ID).
*
* The resulting object (base object class is Postx_Object) will
* hold the $post (object) in addition to its table row data.
*/

class Postx_Model extends Model {
	
	
	public $_object_class = 'Postx_Object';
	
	public $has_meta;
	
	public $always_append_meta = false;
	
	public $_meta_model_class = 'Meta_Model';
	
	public $synced_taxonomies = array(); // associative array of 'taxonomy' => 'table_column' to sync
	
	
	public function has_synced_taxonomies(){
		
		return !empty($this->synced_taxonomies) ? true : false;
	}
	
	public function get_synced_taxonomies(){
		return $this->synced_taxonomies;	
	}
	
	public function is_synced_taxonomy( $tax ){
		return isset($this->synced_taxonomies[ $tax ]) ? true : false;	
	}
	
	public function get_synced_tax_columns( $tax ){
		return $this->synced_taxonomies[ $tax ];	
	}
	
	public function sync_taxonomy_term( $taxonomy, $term_object, $object_id ){
		
		// do something...
	}
	
	function forge_compound_object( &$db_object, $extra_data = array() ){
		
		$object = $this->forgeObject( $db_object );
		
		if ( !empty($extra_data) ){
			foreach($extra_data as $key => $data){
				$object->append_data($key, $data);	
			}
		}
		
		return $object;
	}
	
	
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
		
		$sql = "SELECT * FROM {$this->table} WHERE {$this->primary_key} = {$wp_post->ID}";
		
		return $this->forgeObject( $wpdb->get_row($sql) );
	}
	
	
	public function get_meta( $id, $key, $output = OBJECT ){
		
		if ( !$this->has_meta || !isset($this->_meta_model_class) )
			return false;
		
		$meta_model =& call_user_func(array($this->_meta_model_class, 'instance'));
		
		return $meta_model->get_object( $id, $key, $output );
	}
	
		
}
