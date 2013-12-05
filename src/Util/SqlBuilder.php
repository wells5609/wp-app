<?php
if ( !function_exists("maybe_drop_table") ):

	function maybe_drop_table($table_name, $drop_ddl) {
		global $wpdb;
		foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
			if ($table == $table_name) {
				// found it, try to drop it.
				$wpdb->query($drop_ddl);
			}
		}
		// we cannot directly tell that whether this succeeded!
		foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
			if ($table == $table_name) {
				return false;
			}
		}
		return true;
	}

endif;

class SqlBuilder {
	
	public function create_table( $schema ){
		
		require_once ABSPATH . 'wp-admin/install-helper.php';
		
		$table = $schema['table'];
		
		$sql = self::sql_create_table($schema);
		
		$result = maybe_create_table( $table, $sql );	
				
		return $result;
	}
	
	public function drop_table( $schema ){
		
		$table = $schema['table'];
		
		$sql = self::sql_drop_table($schema);
		
		$result = maybe_drop_table( $table, $sql );	
				
		return $result;	
	}
	
	public function sql_create_table( $schema ){
		
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
		
		foreach($schema['columns'] as $name => $settings){
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
	
	public function sql_drop_table( $schema ){
		
		global $wpdb;

		$table = $schema['table'];
		
		if ( !isset($wpdb->{$schema['table_basename']}) ){
			vardump($schema, $table);
			throw new InvalidArgumentException("Trying to dump invalid table $table");
			return false;
		}
		
		$sql = "DROP TABLE $table";
		
		return $sql;
	}
	
	
}

?>