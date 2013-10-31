<?php

class SqlBuilder {
	
	public function install( $schema ){
		
		require_once ABSPATH . 'wp-admin/install-helper.php';
		
		$install = $results = array();
		
		if ( !is_array($schema) )
			$schema = array($schema);
			
		foreach($schema as $s){
			
			$install[ $s['table'] ] = self::sql_create_table($s);
		}
		
		foreach($install as $table => $sql){
		
			$results[ $table ] = maybe_create_table( $table, $sql );	
		}
		
		return $results;
	}
	
	protected function sql_create_table( $schema ){
		
		global $wpdb;

		// require at least a table (its been constructed) and a pK
		if ( !isset($schema['table']) || empty($schema['primary_key']) )
			throw new InvalidArgumentException('Invalid schema passed. Must construct model first');
		
		$table_basename = $schema['table_basename'];
		$charset_collate = '';
		
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	
		$sql = "CREATE TABLE {$wpdb->$table_basename} (";
		
		foreach($schema['field_names'] as $name => $settings){
			$sql .= "\n  {$name} {$settings},";
		}
		
		$pk = $schema['primary_key'];
		
		$sql .= "\n  PRIMARY KEY  ({$pk}),";
		
		
		if ( !empty($schema['unique_keys']) ){

			foreach($schema['unique_keys'] as $name => $key){
				$sql .= "\n  UNIQUE KEY {$name} ({$key}),";
			}
		}
		
		if ( !empty($schema['keys']) ){
			
			foreach($schema['keys'] as $name => $key){
				$sql .= "\n  KEY {$name} ({$key}),";
			}
		}
		
		$sql = trim($sql, ',');
		
		$sql .= "\n) $charset_collate;";
		
		return $sql;
	}
		
}

?>