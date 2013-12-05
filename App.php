<?php
/**
* AppApi initiates all App components.
* It is basically a filesystem controller with a few extras.
*/

class App {
	
	public $paths = array(); # Directory paths
	
	public $urls = array(); # URLs
	
	public $features = array(); # Features
	
	public $autoload_paths = array(); # Directories to search in for autoloaded classes.
	
	private static $_instance;
		
	final public static function instance() {
		if ( ! isset(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct(){
		
		// Constants
		include 'inc/config.php';
				
		if ( !defined('APP_PATH') ){
			define( 'APP_PATH', trailingslashit( plugin_dir_path(__FILE__) ) );
		}
		if ( defined('APP_REL_PATH') && !defined('APP_URL') ){
			// If APP_REL_PATH is defined, build APP_URL using site URL and relative path.
			define( 'APP_URL', trailingslashit( get_site_url() ) . ltrim( APP_REL_PATH, '/' ) );
		}
		if ( !defined('APP_URL') ){
			define('APP_URL', plugins_url('/', __FILE__));
		}
		
		// Paths
		$this->paths['base']	=	APP_PATH; # Base path
		$this->paths['src']		=	APP_PATH . 'src/'; # Core files
		$this->paths['util']	=	$this->paths['src'] . 'Util/';
		$this->paths['app']		=	APP_PATH . 'app/'; # Custom data objects
		$this->paths['inc']		=	APP_PATH . 'inc/'; # Static PHP includes (e.g. post-types)
		$this->paths['lib']		=	APP_PATH . 'lib/'; # Misc. classes and libraries
		$this->paths['views']	=	APP_PATH . 'views/'; # Views
		$this->paths['assets']	=	APP_PATH . 'assets/'; # Public files (e.g. css & js)		
		
		// URLs
		$this->urls['base']		=	APP_URL;
		$this->urls['assets']	=	APP_URL . 'assets/';
		
		// Actions
		add_action( 'init', array($this, 'early_init'), 0 );
		add_action( 'init', array($this, 'init'), 9 );
		add_action( 'wp_enqueue_scripts', array($this, 'wp_enqueue_scripts') );
		add_action( 'load_ajax_handlers', array($this, 'load_ajax_handlers'));
		
		// Features
		add_action( 'app/load/api', array($this, 'feature_load_api'));
		add_action( 'app/load/cache', array($this, 'feature_load_cache'));
		
		// Autoloading
		$this->autoload_paths = array(
			$this->paths['src'],
			$this->paths['app'],
			$this->paths['inc'],
			$this->paths['lib'],
		);	
		
		if ( function_exists('autoload_paths') ){
			// Using Library plugin with Autoloader class.
			autoload_paths(false, $this->autoload_paths, true);
		}
		else {
			spl_autoload_register(array($this, 'autoloader'));
		}
			
	}
	
	
	function feature_load_api(){
		
		if ( !defined( 'USE_API_FOR_AJAX' ) ){
			// Use http://yoursite.com/api/... for AJAX rather than wp-admin/admin-ajax.php
			define( 'USE_API_FOR_AJAX', true );
		}
		
		if ( USE_API_FOR_AJAX ){
	
			if ( !defined( 'API_AJAX_VAR' ) ){
				// URI component - e.g. http://yoursite.com/api/{API_AJAX_VAR}/
				define( 'API_AJAX_VAR', 'ajax' );
			}
			if ( !defined( 'AJAX_FORCE_NONCE' ) ){
				// Implements router-level nonce verification for all AJAX calls
				define( 'AJAX_FORCE_NONCE', false );
			}
		}
		
		$this->load_file('src', 'Api/Main');
		$this->load_file('src', 'Api/functions.api');
		
		$GLOBALS['api'] =& Api_Main::instance();
	}
	
	function feature_load_cache(){
		
		$this->load_file('lib', 'Cache');
	}
	
	
	/***************************************
			DIRECTORY/FILE METHODS
	***************************************/
		
	/** get_path
	*
	* Returns the path to a file or directory
	*/
	public function get_path($to = 'base', $filepath = false){
		
		if ( !isset($this->paths[$to]) )
			$to = $this->dir_alias($to);
		
		if ( isset($this->paths[$to]) ){
			
			if ( $filepath )
				return $this->paths[$to] . $this->sanitize_path($filepath);
			
			return $this->paths[$to];
		}
		
		$to = $this->translate_dir($to);

		if ( $filepath )
			return $to . $this->sanitize_path($filepath);
		
		return $to;
	}
	
	public function add_path($name, $dirpath){
		
		$this->paths[$name] = $this->sanitize_path($dirpath);
		
		return $this;
	}
	
	
	/** get_url
	*
	* Returns the URL to a file or directory
	*/
	public function get_url($to = 'base', $filepath = NULL){
		
		if ( isset($this->urls[$to]) ) {
			
			if ( $filepath )
				return $this->urls[$to] . $this->sanitize_path($filepath);
			
			return $this->urls[$to];
		}
		
		$to = $this->translate_dir($to);
		
		if ( $filepath )
			return $to . $this->sanitize_path($filepath);
		
		return $to;
	}
	

	/***************************************
					FEATURES	
	***************************************/
	
	public function get_features($enabled = null){
		if ( true === $enabled ){
			$features = array();
			foreach($this->features as $feature => $enabled){
				if ( 1 == $enabled )
					$features[] = $feature;	
			}
			return $features;
		}
		return array_keys( $this->features );
	}

	/** is_feature_enabled
	*
	* Returns array of supported features if $feature = false
	* If $feature, returns true if feature is supported, otherwise false.
	*/
	public function is_feature_enabled($feature){
		return isset($this->features[$feature]) && 1 == $this->features[$feature];	
	}
	
	/**
	* Enables a feature
	*
	* @param string $feature The feature to enable
	*/
	public function enable_feature($feature, $preload = true){
		
		if ( !$this->is_feature_enabled($feature) )
			$this->features[$feature] = 1;
		
		if ( $preload )
			$this->load_feature($feature);
		
		return $this;
	}
	
	/**
	* Removes support for a feature
	*
	* @param string $feature The feature to remove
	*/
	public function disable_feature($feature){
		if ( isset($this->features[$feature]) ){
			unset($this->features[$feature]);
		}
		return $this;
	}
	
	// Loads a feature
	public function load_feature( $feature ){
		
		$feature = strtolower($feature);
		
		do_action("app/load/{$feature}");			
	}
	
	
	/***************************************
					VIEWS
	***************************************/
	
	/**
	* Loads a view using require
	*
	* @param string $view The view to load - can be dot-concatenated, e.g. "group.view"
	* @param boolean|string $vars Associative array of variables to localize in view
	* @return void
	*/
	public function load_view($view, &$vars = array()){
		
		$view = trim($view, '\\/');
		
		if ( strpos($view, '.') !== false ){
			$_parts = explode('.', $view);
			
			$group = trim( $this->dir_alias($_parts[0]), '\\/');
			$view = $_parts[1];
			
			$file = $this->paths['views'] . "{$group}/{$view}.php";
			
			if ( file_exists($file) ){
				
				if ( !empty($vars) ) extract($vars, EXTR_SKIP);	
				
				do_action("app/load_view/before/{$group}", $view);
				
				include $file;
			}
		}
		else {
			if ( !empty($vars) ) extract($vars, EXTR_SKIP);	
				
			do_action('app/load_view/before', $view);
			
			$this->load_file('views', $view, 'include');
		}
	}
	
	public function load_view_section($func, &$args = array()){
				
		if ( is_string($func) ){
			
			if ( strpos($func, '.') === false ){
				
				$func = "view_section_{$func}";
			}
			else {
				
				$base = substr( $func, 0, strpos($func, '.') );
				
				$method = trim(str_replace('.', '_', str_replace($base, '', $func)), '_');
				
				$func = array( ucfirst($base) . '_ViewSections', $method );
			}
		}
		
		if ( !is_array($args) )
			$args = array($args);
		
		if ( is_callable($func) ){
			return call_user_func( $func, $args );
		}
	}

	
	/***************************************
				UTILITY FUNCTIONS
	***************************************/
	
	// Sanitize a file path
	public function sanitize_path($var){
		$var = ltrim($var, '/\\');
		if ( is_dir($var) )
			$var = trailingslashit($var);
		return $var;
	}
		
	/**
	* Translates a directory name to a key of $paths array
	*/
	public function dir_alias($dirname){
		switch ($dirname) {
			case 'includes': return 'inc';
				break;
			case 'classes': return 'lib';
				break;
			case 'public': return 'assets';
				break;
			case 'view': return 'views';
				break;
			case 'core': return 'src';
				break;
			default: return apply_filters( 'app/dir_alias', $dirname );
		}	
	}
		
	/**
	* Translates a dirname into a full path
	*
	* Cannot be accessed by $this->{dirname}
	* Use $this->get_path()
	*/
	public function translate_dir($name){
		switch ($name) {
			case 'js':
				return $this->paths['assets'] . 'js/';
				break;
			case 'css':
				return $this->paths['assets'] . 'css/';
				break;
			default: return apply_filters('app/translate_dir', $name);
		}	
	}
	
	
	/***************************************
				INIT ACTIONS	
	***************************************/
	
	/** eary_init
	* 
	* Includes post-types, statuses, taxonomies, fields, etc.
	* Includes function files, init file, and ajax callbacks
	*
	* Called on 'init' w/ priority 1 (default)
	*/
	function early_init(){
		
		$inc = $this->paths['inc'];
		
		foreach(array('post-types', 'stati', 'taxonomies', 'fields') as $dir){
			if ( is_dir("$inc/$dir") ){
				foreach(glob("$inc/$dir/*.php") as $file)
					include $file;
			}	
		}
		
		$this->load_file('src', 'functions');
		
		$this->load_file('app', 'functions');

		$this->load_file('app', 'hooks');
		
		$this->load_file('app', 'init');
		
		do_action('app/early_init');
	}
	
	// 'init' w/ priority 10 (default)
	function init(){
		
		if ( is_admin() ){
	
			$this->load_file('src', 'admin/app-tables');
		}
				
		do_action('app/init');
	}
	
	// plugin wpajax action
	function load_ajax_handlers(){
		$this->load_file('app', 'ajax-callbacks');	
	}
	
	// 'wp_enqueue_scripts'
	function wp_enqueue_scripts(){
		
		wp_deregister_script('jquery');
		wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', array(), '', false);
		
		$this->load_file('assets', 'scripts-styles');
	}
	
	
	/***************************************
				FILE LOADERS	
	***************************************/
	
	// Load a file if it exists
	public function load_file($path = 'base', $filename = null, $load_how = 'include', $ext = 'php'){
		
		if ( isset($this->paths[$path]) ){
			if ( null === $filename ){
				throw new Exception('load_file requires filename for named paths');	
			}
			$path = $this->paths[$path] . $filename . '.' . $ext;	
		}
		
		if ( file_exists( $path ) ){
			switch($load_how){
				case 'include_once':
					include_once $path;
					break;
				case 'include':
					include $path;
					break;
				case 'require_once':
					require_once $path;
					break;
				case 'require':
				default:
					require $path;
					break;	
			}
			return true;
		}
		return false;
	}
	
	/** autoloader
	*/
	protected function autoloader($class){
		
		$class = str_replace('_', '/', $class) . '.php';
		
		foreach($this->autoload_paths as $path){
			
			if ( file_exists($path . $class) ){
				include $path . $class;
				return true;	
			}
		}
	
	}
	
	final function __clone(){
		trigger_error(__CLASS__ . ' can not be cloned!', E_USER_ERROR);	
	}
	final function __sleep(){
		trigger_error(__CLASS__ . ' can not be serialized!', E_USER_ERROR);
	}
	
}

/*******************************
		APP API FUNCTIONS					
*******************************/

// get the AppApi instance
function app_instance(){
	return App::instance();
}
// Load a view
function app_load_view($view, &$vars = array()){
	return App::instance()->load_view($view, $vars);
}
// Load a view section
function load_view_section($func, &$vars = array()){
	return App::instance()->load_view_section($func, $vars);
}
// Get a path
function app_get_path($to = 'base', $file = NULL){
	return App::instance()->get_path($to, $file);	
}
// Get a URL
function app_get_url($to = 'base', $file = NULL){
	return App::instance()->get_url($to, $file);	
}

App::instance(); // init