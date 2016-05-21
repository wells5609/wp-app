<?php

namespace WordPress\Database\Table\Command;

use WordPress\Database\Table\Command;

/**
 * DROP TABLE command.
 */
class Drop extends Command
{
	
	private $success;
	
	public function success() {
		return $this->success;
	}
	
	public function __invoke() {
		
		global $wpdb;
		
		foreach ($wpdb->get_col("SHOW TABLES", 0) as $table) {
			if ($table == $this->schema->name) {
				$wpdb->query($this->sql());
				break;
			}
		}
		
		$this->success = ! in_array($this->schema->name, $wpdb->get_col("SHOW_TABLES", 0));
	}
	
	public function sql() {
		global $wpdb;
		return "DROP TABLE {$wpdb->prefix}{$this->schema->name}";
	}
	
}
