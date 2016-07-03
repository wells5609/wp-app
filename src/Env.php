<?php

namespace WordPress;

class Env
{
	
	/**
	 * The environment type name
	 * @var string
	 */
	public $name;
	
	/**
	 * Path to the root installation directory.
	 * @var string
	 */
	public $root;
	
	/**
	 * Path to the root web directory.
	 * @var string
	 */
	public $webroot;
	
	/**
	 * Environment mode: "http" or "cli".
	 * @var string
	 */
	public $mode;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->webroot = dirname(ABSPATH);
		$this->root = dirname($this->webroot);
		$this->name = WP_ENV;
		$this->mode = php_sapi_name() === 'cli' ? 'cli' : 'http';
	}
	
	/**
	 * Returns the value of an environment variable.
	 * 
	 * Alias of getenv()
	 * 
	 * @param string $name
	 * 
	 * @return mixed
	 */
	public static function get($name) {
		return getenv($name);
	}
	
	/**
	 * Alias of getenv()
	 * 
	 * @param string $var
	 * 
	 * @return mixed
	 */
	public function set($varname, $value) {
		return getenv($var);
	}
	
	/**
	 * Returns the site name.
	 * 
	 * @return string
	 */
	public function getName() {
		return get_option('blogname');
	}
	
	/**
	 * Returns the path to the WordPress installation directory with a trailing slash.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getWordPressPath($path = '') {
		return rtrim(ABSPATH, '/').'/'.$path;
	}
	
	/**
	 * Returns the path to the content directory with a trailing slash.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getContentPath($path = '') {
		return rtrim(WP_CONTENT_DIR, '/').'/'.$path;
	}
	
	/**
	 * Returns the path to the plugins directory with a trailing slash.
	 * 
	 * @return string
	 */
	public function getPluginsPath() {
		return WP_PLUGIN_DIR.'/';
	}
	
	/**
	 * Returns the path to the must-use plugins directory with a trailing slash.
	 * 
	 * @return string
	 */
	public function getMuPluginsPath() {
		return WPMU_PLUGIN_DIR.'/';
	}
	
	/**
	 * Returns the path to the wp-admin directory with a trailing slash.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getAdminPath($path = '') {
		return $this->getWordPressPath('wp-admin/'.$path);
	}
	
	/**
	 * Returns the path to the user application directory with a trailing slash.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getAppPath($path = '') {
		return $this->root.'/app/'.$path;
	}
	
	/**
	 * Returns the path to the active theme directory with a trailing slash.
	 * 
	 * @return string
	 */
	public function getThemePath() {
		return rtrim(get_stylesheet_directory(), '/').'/';
	}
	
	/**
	 * Returns the home URL.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getUrl($path = '') {
		return get_home_url(null, $path);
	}
	
	/**
	 * Returns the admin URL.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getAdminUrl($path = '') {
		return get_admin_url(null, $path, 'admin');
	}
	
	/**
	 * Returns whether currently in admin area.
	 * 
	 * @return boolean
	 */
	public function isAdmin() {
		return is_admin();
	}
	
	/**
	 * Whether the current request came from the web server.
	 * 
	 * @return boolean
	 */
	public function isHttp() {
		return $this->mode === 'http';
	}
	
	/**
	 * Whether the current request came from the command line.
	 * 
	 * @return boolean
	 */
	public function isCli() {
		return $this->mode === 'cli';
	}
	
}
