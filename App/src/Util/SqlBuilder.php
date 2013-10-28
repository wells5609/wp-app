<?php

class SqlBuilder {
	
	public function install( $schema ){
		
		require_once ABSPATH . 'wp-admin/install-helper.php';
		
		$install = array();
		
		if ( is_array($schema) ){
			
			foreach($schema as $s){
				
				if ( !$s instanceof Schema )
					throw new InvalidArgumentException('schemas must be an instance of Schema');
				
				$install[ $s->table ] = self::create_table($s);
			}
		}
		
		elseif ( !$schema instanceof Schema )
			throw new InvalidArgumentException('schema must be an instance of Schema');
			
		$install[ $schema->table ] = self::create_table($schema);
		
		$results = array();
		
		foreach($install as $table => $sql){
		
			$results[ $table ] = maybe_create_table( $table, $sql );	
		}
		
		return $results;
	}
	
	protected function create_table( Schema $schema ){
		
		global $wpdb;		
		$table_basename = $schema->table_basename;
		$charset_collate = '';
		
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	
		$sql = "CREATE TABLE {$wpdb->$table_basename} (";
		
		foreach($schema->field_names as $name => $settings){
			$sql .= "\n  {$name} {$settings},";
		}
		
		$sql .= "\n  PRIMARY KEY  ({$schema->primary_key}),";
		
		
		if ( !empty($schema->unique_keys) ){

			foreach($schema->unique_keys as $name => $key){
				$sql .= "\n  UNIQUE KEY {$name} ({$key}),";
			}
		}
		
		if ( !empty($schema->keys) ){
			
			foreach($schema->keys as $name => $key){
				$sql .= "\n  KEY {$name} ({$key}),";
			}
		}
		
		$sql = trim($sql, ',');
		
		$sql .= "\n) $charset_collate;";
		
		return $sql;
	}
		
}

?>