<?php

/**
 * Returns a database Table by name.
 *
 * @param string $name
 *
 * @return \WordPress\Database\Table
 */
function db_get_table($name) {
	return WordPress\Database\Connection::instance()->getTable($name);
}

/**
 * Checks whether a given database table exists.
 * 
 * @param string $name
 * @param boolean $reset
 * 
 * @return boolean
 */
function db_table_exists($name, $reset = false) {
	return WordPress\Database\Connection::instance()->isTableInstalled($name, $reset);
}

/**
 * Returns an array of installed database table names.
 *
 * @param boolean $reset [Optional] Default = false
 *
 * @return array
 */
function db_get_installed_tables($reset = false) {
	return WordPress\Database\Connection::instance()->getTableNames($reset);
}

/**
 * Returns an array of the WordPress database tables.
 * 
 * @return array
 */
function db_get_builtin_tables() {
	return array('posts', 'comments', 'links', 'options', 'postmeta', 'terms', 'term_taxonomy', 
		'term_relationships', 'termmeta', 'commentmeta', 'users', 'usermeta');
}

if (! function_exists("maybe_drop_table")) :
	
	/**
	 * Attempts to drop a database table if it exists.
	 * 
	 * @param string $table_name
	 * @param string $drop_ddl
	 * @return boolean Whether the table exists.
	 */
	function maybe_drop_table($table_name, $drop_ddl) {
		
		global $wpdb;
		if (strpos($table_name, $wpdb->prefix) !== 0) {
			$table_name = $wpdb->prefix.$table_name;
		}
		
		$dropped = false;
		
		foreach ($wpdb->get_col('SHOW TABLES', 0) as $table) {
			if ($table === $table_name) {
				$GLOBALS['wpdb']->query($drop_ddl);
				$dropped = true;
				break;
			}
		}
		
		return $dropped ? db_table_exists($table_name, true) : false;
	}

endif;
