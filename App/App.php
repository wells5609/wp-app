<?php
/**
* AppApi initiates all App components.
* It is basically a filesystem controller with a few extras.
*/

class AppApi {
	
	// AppApi instance
	protected static $instance;
	
	// App dir paths
	public $paths = array();
	
	// App URLs
	public $urls = array();
	
	// Features
	public $supports = array();
	
	
	public static function instance() {
		if ( ! isset(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct(){
		
		if ( ! defined("APP_PATH") )
			define("APP_PATH", plugin_dir_path(__FILE__));
		
		if ( defined("APP_REL_PATH") && ! defined("APP_URL") )
			define("APP_URL", trailingslashit(get_site_url()) . ltrim(APP_REL_PATH, '/'));
			
		if ( ! defined("APP_URL") )
			define("APP_URL", plugins_url('', __FILE__));
		
		if ( ! defined('VALUE') )
			define('VALUE', 'VALUE');
		
		/** Paths */
		
		$this->paths['base']	=	trailingslashit( APP_PATH );
		
		# src/ - core files
		$this->paths['src']		=	$this->paths['base'] . 'src/';
		$this->paths['util']	=	$this->paths['src'] . 'Util/';
		
		# app/ - custom data objects
		$this->paths['app']		=	$this->paths['base'] . 'app/';
		
		# inc/ - static includes (e.g. post-types)
		$this->paths['inc']		=	$this->paths['base'] . 'inc/';
		
		# lib/ - misc. classes and libraries
		$this->paths['lib']		=	$this->paths['base'] . 'lib/';
		
		# views/ - views
		$this->paths['views']	=	$this->paths['base'] . 'views/';
		
		# assets/ - public files (e.g. css & js)
		$this->paths['assets']	=	$this->paths['base'] . 'assets/';
		
		/** URLs */
		$this->urls['base']		=	trailingslashit( APP_URL );
		$this->urls['assets']	=	$this->urls['base'] . 'assets/';	
		
		/** Init actions */
		
		add_action( 'init', array($this, 'early_init'), 1 );
		
		add_action( 'init', array($this, 'init'), 10 );
		
		add_action( 'wp_enqueue_scripts', array($this, 'wp_enqueue_scripts') );
				
		spl_autoload_register(array($this, 'autoloader'));			

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
				FEATURE SUPPORT	
	***************************************/

	/** supports
	*
	* Returns array of supported features if $feature = false
	* If $feature, returns true if feature is supported, otherwise false.
	*/
	public function supports($feature = false){
		if ( ! $feature )
			return array_keys( $this->supports );	
		return isset($this->supports[$feature]) && 1 == $this->supports[$feature];	
	}
	
	/**
	* Adds support for a feature
	*
	* @param string $feature The feature to support
	* @return integer Returns 1 if support added, 2 if feature already supported.
	*/
	public function add_support($feature, $preload = true){
		
		if ( ! $this->supports($feature) )
			$this->supports[$feature] = 1;
		
		if ( $preload )
			$this->load_feature($feature);
		
		return $this;
	}
	
	/**
	* Removes support for a feature
	*
	* @param string $feature The feature to remove
	* @return integer Returns 1 if support removed, 2 if feature was not supported to begin with.
	*/
	public function remove_support($feature){
		if ( $this->supports($feature) ){
			unset( $this->supports[$feature] );
		}
		return $this;
	}
	
	
	/***************************************
					VIEWS
	***************************************/
	
	/**
	* Loads a view using require
	*
	* @param string $view The view to load
	* @param boolean|string $group The view group - a subdirectory holding the view file.
	* @return void
	*/
	public function load_view($view, $group = false){
		
		$view = trim($view, '\\/');
		
		if ( $group ){
			$group = trim( $this->dir_alias($group), '\\/');
			$file = $this->paths['views'] . "{$group}/{$view}.php";
			if ( file_exists($file) ){
				do_action("app/load_view/before/{$group}", $view);
				include $file;
				do_action("app/load_view/after/{$group}", $view);
			}
		}
		else{
			do_action('app/load_view/before', $view);
			$this->load_file('views', $view, 'include');
			do_action('app/load_view/after', $view);
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
			
			case 'includes':
				return 'inc:';
				break;
				
			case 'classes':
				return 'lib';
				break;
						
			case 'public':
				return 'assets';
				break;
			
			case 'view':
				return 'views';
				break;
			
			case 'core':
				return 'src';
				break;
			
			default:
				return apply_filters( 'app/dir_alias', $dirname );
				break;
		}	
	}
		
	/**
	* Translates a dirname into a full dir path
	*
	* Basically allows you to add paths without actually adding them as a var.
	* However, they (obviously) cannot be accessed by $this->{dirname}
	* Instead, use $this->get_path()
	*/
	public function translate_dir($name){
		
		switch ($name) {
			
			case 'js':
				return $this->paths['assets'] . 'js/';
				break;
				
			case 'css':
				return $this->paths['assets'] . 'css/';
				break;
		
			default:
				return apply_filters('app/translate_dir', $name);
				break;
		}	
	}
	
	
	/***************************************
				INIT ACTIONS	
	***************************************/
		
	/** eary_init
	* 
	* Includes post-types, statuses, taxonomies, and fields
	*
	* Called on 'init' w/ priority 1 (default)
	*/
	function early_init(){
		
		$inc = $this->paths['inc'];
		
		$dirs = array(
			'post-types',
			'stati',
			'taxonomies',
			'fields',
		);
		
		foreach($dirs as $dir){
			if ( is_dir("$inc/$dir") ){
				foreach(glob("$inc/$dir/*.php") as $file)
					require_once $file;
			}	
		}
		
		$this->load_file('app', 'init');
		
		$this->load_file('inc', 'functions');
				
		do_action('app/early_init');
	}
	
	
	// 'init' w/ priority 10 (default)
	function init(){
		
		if ( is_admin() ){
			include_once APP_PATH . 'src/admin/app-tables.php';	
		}
		
		do_action('app/init');
	}
		
	
	// 'wp_enqueue_scripts'
	function wp_enqueue_scripts(){
		
		$this->load_file('assets', 'scripts-styles', 'include_once');
	}
	
	
	// Loads a feature
	function load_feature( $feature ){
		
		$feature = strtolower($feature);
		
		switch($feature){
			
			case 'postx':
				ManagerRegistry::instance()->register('postx');
				break;
			
			case 'meta':
				ManagerRegistry::instance()->register('meta');
				break;
				
			default:
				do_action('app/load_feature', $feature);
				break;	
		}
						
	}
	
	
	/***************************************
				FILE INCLUDERS	
	***************************************/
	
	// Require once a file if it exists
	public function load_file($path = 'base', $filename = null, $load_how = 'require_once', $ext = 'php'){
		
		if ( isset($this->paths[$path]) ){
			if ( null === $filename ){
				throw new Exception('load_file requires filename for named paths');	
			}
			$path = $this->paths[$path] . "{$filename}.{$ext}";	
		}
		
		if ( file_exists( $path ) ){
			switch($load_how){
				case 'include_once':
					include_once $path;
					break;
				case 'include':
					include $path;
					break;
				case 'require':
					require $path;
					break;
				case 'require_once':
				default:
					require_once $path;
					break;	
			}
			return true;
		}
		return false;
	}
	
	/** Class autoloader
	*
	*/
	function autoloader($class){
		
		//	Try with underscores converted to directory separators
		if ( strpos($class, '_') !== false ){
			
			$_class = str_replace('_', '/', $class);
			
			if ( $this->load_file('src', $_class) )
				return;
			if ( $this->load_file('app', $_class) )
				return;
			if ( $this->load_file('lib', $_class) )
				return;	
		}
		
		$this->autoload_translate($class);
		
		//	Look in src, app, lib
		if ( $this->load_file('src', $class) )
			return;
		if ( $this->load_file('app', $class) )
			return;
		if ( $this->load_file('lib', $class) )
			return;		
	
	}
	
	private function autoload_translate(&$class){
		
		if ( strpos($class, 'Registry') != 0 ){
			$class = 'registries/' . $class;	
		}
		elseif ( strpos($class, 'Interface') != 0 ){
			$class = 'interfaces/' . $class;	
		}
		elseif ( $pos = strpos($class, 'Manager') ){
			$type = substr($class, 0, $pos);
			$class = $type . '/' . $class;
		}
		
	}
	
	
	// psst, singleton.
	final function __clone(){
		trigger_error(__CLASS__ . ' can not be cloned!', E_USER_ERROR);	
	}
	final function __sleep(){
		trigger_error(__CLASS__ . ' can not be serialized!', E_USER_ERROR);
	}
	
}

AppApi::instance(); // init

/*******************************
		APP API FUNCTIONS					
*******************************/

// get the AppApi instance
function app_api_instance(){
	return AppApi::instance();
}
// Load a view
function app_load_view($view, $group = false){
	return AppApi::instance()->load_view($view, $group);
}
// Get a path
function app_get_path($to = 'base', $file = NULL){
	return AppApi::instance()->get_path($to, $file);	
}
// Get a URL
function app_get_url($to = 'base', $file = NULL){
	return AppApi::instance()->get_url($to, $file);	
}