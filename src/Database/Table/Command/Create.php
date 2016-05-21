<?php

namespace WordPress\Database\Table\Command;

use WordPress\Database\Table\Command;

/**
 * CREATE TABLE command.
 */
class Create extends Command
{
	
	const INCLUDE_FILE_PATH = 'wp-admin/install-helper.php';
	
	private $success;
	
	public function success() {
		return $this->success;
	}
	
	public function __invoke() {
		$this->loadIncludes();
		$this->success = maybe_create_table($this->schema->name, $this->sql());
	}
	
	public function sql() {
		
		global $wpdb;
		
		$charset_collate = '';
		
		if (! empty($wpdb->charset)) {
			$charset_collate .= "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		
		if (! empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
		
		$sql = "CREATE TABLE {$wpdb->prefix}{$this->schema->name} (";
		
		foreach($this->schema->columns as $column => $settings) {
			$sql .= "\n  {$column} {$settings},";
		}
		
		// double space after pk
		$sql .= "\n  PRIMARY KEY  ({$this->schema->primary_key}),";
		
		foreach($this->schema->unique_keys as $name => $key) {
			$sql .= "\n  UNIQUE KEY {$name} ({$key}),";
		}
		
		foreach($this->schema->keys as $name => $key){
			$sql .= "\n  KEY {$name} ({$key}),";
		}
	
		$sql = trim($sql, ',') . "\n) {$charset_collate};";
		
		return $sql;
	}
	
	private function loadIncludes() {
		require_once ABSPATH . self::INCLUDE_FILE_PATH;
	}
	
}
