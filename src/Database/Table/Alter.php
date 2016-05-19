<?php

namespace WordPress\Database\Table;

use RuntimeException;

class Alter 
{
	
	/**
	 * @var \WordPress\Database\Table\Schema
	 */
	protected $schema;
	
	public function __construct(Schema $schema) {
		if (! $schema->validate()) {
			throw new RuntimeException('Table schema must have at least a "name" and "primary_key".');
		}
		$this->schema = $schema;
	}
	
	public function install() {
		require_once ABSPATH.'wp-admin/install-helper.php';
		return maybe_create_table($this->schema->name, static::installSql($this->schema));
	}
	
	public function drop() {
		
		global $wpdb;
		
		foreach ($wpdb->get_col("SHOW TABLES", 0) as $table) {
			if ($table == $this->schema->name) {
				// found it, try to drop it.
				$wpdb->query(static::dropSql($this->schema));
			}
		}
		
		// we cannot directly tell that whether this succeeded
		if (in_array($this->schema->name, $wpdb->get_col("SHOW_TABLES", 0))) {
			return false;
		}
		
		return true;
	}
	
	public static function installSql(Schema $schema) {
		
		/**
		 * @var \wpdb
		 */
		global $wpdb;
						
		if (! $schema->validate()) {
			throw new RuntimeException('Table schema must have at least a "name" and "primary_key".');
		}
		
		$charset_collate = '';
		
		if (! empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		
		if (! empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
		
		$sql = "CREATE TABLE {$wpdb->prefix}{$schema->name} (";
		
		foreach($schema->columns as $column => $settings) {
			$sql .= "\n  {$column} {$settings},";
		}
		
		// double space after pk
		$sql .= "\n  PRIMARY KEY  (".$schema->primary_key.'),';
		
		if (! empty($schema->unique_keys)) {
			foreach($schema->unique_keys as $name => $key) {
				$sql .= "\n  UNIQUE KEY {$name} ({$key}),";
			}
		}
		
		if (! empty($schema->keys)) {
			foreach($schema->keys as $name => $key){
				$sql .= "\n  KEY {$name} ({$key}),";
			}
		}
		
		$sql = trim($sql, ',') . "\n) {$charset_collate};";
		
		return $sql;
	}
	
	public static function dropSql(Schema $schema) {
		global $wpdb;
		return "DROP TABLE {$wpdb->prefix}{$schema->name}";
	}
	
}
