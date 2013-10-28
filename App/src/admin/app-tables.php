<?php

class AppAdmin_InstallTables {
	
	private static $instance;
	
	public static $schemas = array();
	
	public static $tables = array();
	
	public static function instance() {
		if ( !isset(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
	}
	
	public function admin_menu(){
		$page = add_submenu_page('tools.php', 'App Tables', 'App Tables', 'manage_options', 'app-tables', array($this, 'admin_page'));
	}
	
	public function admin_page(){
		if ( ! current_user_can('manage_options') )
			return 'You are not authorized to view this page.';
		require 'app-tables-page.php';
	}
	
	public function get_schemas(){
		
		return ManagerRegistry::instance()->get_all_schemas();
	}
	
	public function get_schema_from_table( $table ){
		
		foreach($this->get_schemas() as $schema){
			
			if ( $schema->table_basename == $table || $schema->table == $table )
				return $schema;
		}	
	}
	
	public function get_tables( $type = null ){
		global $wpdb;
		
		$tables = $installed_tables = array();
		
		foreach($this->get_schemas() as $schema){
			$tables[$schema->table_basename] = $wpdb->prefix . $schema->table_basename;
		}
		if ( 'registered' == $type )
			return $tables;
		
		foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
			if ( in_array($table, $tables) ) {
				$installed_tables[ str_replace($wpdb->prefix, '', $table) ] = $table;
			}
		}
		if ( 'installed' == $type )
			return $installed_tables;
		
		self::$tables = array('registered' => $tables, 'installed' => $installed_tables);
		
		return self::$tables;
	}
	
	
	/** page_request
	*
	*	Processes active component update if requested
	*/
	private function page_request($all_tables, &$installed_tables){
		
		// _wpnonce field is present => means form was submitted
		if (!empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], "update-options")) {
			
			// Check that at least 1 action and table was selected
			if (( !empty($_REQUEST['action']) || !empty($_REQUEST['action2']) ) 
			&& ( !empty($_REQUEST['table']) || !empty($_REQUEST['tables']) )) {
				
				if (!empty($_REQUEST['action']))
					$action = $_REQUEST['action'];
				else $action = $_REQUEST['action2'];
				
				// Bulk edit
				if (!empty($_REQUEST['tables']))
					$tables = $_REQUEST['tables'];
				// Single edit
				else $tables = array($_REQUEST['table']);
				
				$successes = array();
				
				foreach ($tables as $table) :
					
					// validate table
					if ( in_array($table, $all_tables) ) {
						
						// we are installing and table is not => add to array
						if ( $action == 'install' && ! in_array($table, $installed_tables) ) {
							
							$schema = $this->get_schema_from_table($table);
							
							$successes[$table] = SqlBuilder::install($schema);
							
							$installed_tables[] = $table;
						}
						
						// we are deactivating
						else if ( $action == 'drop' ) {
							// table is found in $installed_tables => remove from array
							$index = array_search($table, $installed_tables);
							if ($index !== false)
								unset($installed_tables[$index]);
								
							# do deactivation...
							
						}
					}
				
				endforeach;
				
				return $successes;
			}
		}
		
	}
	
}

AppAdmin_InstallTables::instance();

?>