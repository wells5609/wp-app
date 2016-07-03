<?php

namespace WordPress\Database\Table\Command;

use WordPress\Database\Table\Command;
use WordPress\Database\Connection;
use RuntimeException;

class Create extends Command
{
	
	public function __invoke() {
		require_once ABSPATH.'wp-admin/install-helper.php';
		return maybe_create_table($this->schema->getName(), $this->getSql());
	}
	
	public function getSql() {
		
		$db = Connection::instance();
		$name = $this->schema->getName();
		$primary_key = $this->schema->getPrimaryKey();
		$columns = $this->schema->getColumns();
		$unique_keys = $this->schema->getUniqueKeys();
		$keys = $this->schema->getKeys();
		
		if (! $this->schema->validate()) {
			throw new RuntimeException("Invalid database table schema ('{$this->schema->getName()}')");
		}
		
		$charset_collate = '';
		if ($charset = $db->getCharset()) {
			$charset_collate = "DEFAULT CHARACTER SET {$charset}";
		}
		if ($collate = $db->getCollate()) {
			$charset_collate .= " COLLATE {$collate}";
		}
		
		$sql = "CREATE TABLE IF NOT EXISTS {$db->getTablePrefix()}{$name} (";
		
		foreach($columns as $column => $settings) {
			$sql .= "\n  {$column} {$settings},";
		}
		
		// double space after pk
		$sql .= "\n  PRIMARY KEY  ({$primary_key}),";
		
		foreach($unique_keys as $name => $key) {
			$sql .= "\n  UNIQUE KEY {$name} ({$key}),";
		}
		
		foreach($keys as $name => $key){
			$sql .= "\n  KEY {$name} ({$key}),";
		}
		
		$sql = trim($sql, ',') . "\n) {$charset_collate};";
		
		return $sql;
	}
	
}
