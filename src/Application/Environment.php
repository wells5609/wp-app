<?php

namespace WordPress\Application;

class Environment
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
	public function __construct($webroot, $root = null, $name = null, $mode = null) {
		#$this->webroot = dirname(ABSPATH);
		#$this->root = dirname($this->webroot);
		$this->webroot = rtrim($webroot, '/\\');
		$this->root = rtrim($root ?: $webroot, '/\\');
		$this->name = $name ?: WP_ENV;
		$this->mode = $mode ?: (php_sapi_name() === 'cli' ? 'cli' : 'http');
	}

	/**
	 * Returns the filesystem path to the root installation directory.
	 *
	 * @return string
	 */
	public function getRoot() {
		return $this->root;
	}
	
	/**
	 * Returns the filesystem path to the root web (public) directory.
	 *
	 * @return string
	 */
	public function getWebRoot() {
		return $this->webroot;
	}
	
	/**
	 * Returns the environment name (e.g. "production", "development", etc.)
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns the environment mode: either "http" or "cli".
	 *
	 * @return string
	 */
	public function getMode() {
		return $this->mode;
	}

	/**
	 * Whether the current request came from the command line.
	 *
	 * @return boolean
	 */
	public function isCli() {
		return $this->mode === 'cli';
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
	 * Returns the site name.
	 * 
	 * @return string
	 */
	public function getSiteName() {
		return get_option('blogname');
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

}
