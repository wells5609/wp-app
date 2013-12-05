<?php
class AppAdmin_InstallTables {
	
	private static $instance;
	
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
		
		// Add print scripts and styles action based off the option page hook
        //add_action( 'admin_print_scripts-' . $page, array( $this, 'admin_scripts' ) );
        add_action( 'admin_print_styles-' . $page, array( $this, 'admin_styles' ) );
	}
	
	function admin_styles(){
?>
<style type="text/css">
.
</style>
<?php	
	}
	
	public function admin_page(){
		if ( ! current_user_can('manage_options') )
			return 'You are not authorized to view this page.';
		
		App::instance();
		require 'app-tables-page.php';
	}
	
	public function get_schema_by_table( $table ){
		
		foreach( get_schemas() as $name => $schema ){
			
			if ( $schema['table_basename'] == $table || $schema['table'] == $table )
				return $schema;
		}	
	}
	
	public function get_tables( $type = null ){
		global $wpdb;
		
		$tables = $installed_tables = array();
		
		foreach( get_schemas() as $schema ){
			
			$tables[ $schema['table_basename'] ] = $wpdb->prefix . $schema['table_basename'];
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
		
		return array('registered' => $tables, 'installed' => $installed_tables);
	}
	
	
	/** page_request
	*
	*	Processes active component update if requested
	*/
	private function page_request($all_tables, &$installed_tables){
		
		// _wpnonce field is present => means form was submitted
		if ( !empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], "update-options") ) {
			
			// Check that at least 1 action and table was selected
			if ( !empty($_REQUEST['action']) && !empty($_REQUEST['table']) ) {
				
				require APP_PATH . '/src/Util/SqlBuilder.php';
				
				$action = $_REQUEST['action'];
				$tables = array( $_REQUEST['table'] );
				$successes = array();
				
				foreach ($tables as $table) :
					
					// validate table
					if ( in_array($table, $all_tables) ) {
						
						// get schema
						$schema = $this->get_schema_by_table($table);
												
						// we are installing and table is not installed => add to array
						if ( 'install' == $action && !in_array($table, $installed_tables) ) {
							
							$successes['install'][ $table ] = SqlBuilder::create_table($schema);
							
							$installed_tables[] = $table;
						}
						
						// we are deactivating
						else if ( $action == 'drop' ) {
							
							// table is found in $installed_tables => remove from array
							$index = array_search($table, $installed_tables);
							
							if ($index !== false)
								unset($installed_tables[$index]);
							
							$successes['drop'][ $table ] = SqlBuilder::drop_table($schema);
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