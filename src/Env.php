<?php

namespace WordPress;

class Env
{
	
	/**
	 * Environment type name. 
	 * 
	 * Value is copied from the WP_ENV constant.
	 * 
	 * @var string
	 */
	public $name;
	
	/**
	 * Whether running in a development environment.
	 * 
	 * Equals true if WP_ENV === "development", otherwise false.
	 * 
	 * @var bool
	 */
	public $dev;
	
	/**
	 * Whether this is a Bedrock installation.
	 * 
	 * Equals true if $GLOBALS['root_dir'] is set, otherwise false.
	 * 
	 * @var bool
	 */
	public $bedrock;
	
	/**
	 * Path to the root installation directory with a trailing slash.
	 * 
	 * @var string
	 */
	public $root;
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name = WP_ENV;
		$this->dev = $this->name === 'development';
		if (isset($GLOBALS['root_dir'])) {
			$this->bedrock = true;
			$this->root = trailingslashit($GLOBALS['root_dir']);
		} else {
			$this->bedrock = false;
			$this->root = trailingslashit(ABSPATH);
		}
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
	public function get($name) {
		return getenv($name);
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
	 * Returns the site description.
	 * 
	 * @return string
	 */
	public function getDescription() {
		return get_bloginfo('blogdescription');
	}
	
	/**
	 * Returns the path to the WordPress installation directory with a trailing slash.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getWordPressPath($path = '') {
		return trailingslashit(ABSPATH).$path;
	}
	
	/**
	 * Returns the path to the content directory with a trailing slash.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getContentPath($path = '') {
		return trailingslashit(WP_CONTENT_DIR).$path;
	}
	
	/**
	 * Returns the path to the plugins directory with a trailing slash.
	 * 
	 * @return string
	 */
	public function getPluginsPath() {
		return trailingslashit(WP_PLUGIN_DIR);
	}
	
	/**
	 * Returns the path to the must-use plugins directory with a trailing slash.
	 * 
	 * @return string
	 */
	public function getMuPluginsPath() {
		return trailingslashit(WPMU_PLUGIN_DIR);
	}
	
	/**
	 * Returns the path to the active theme directory with a trailing slash.
	 * 
	 * @return string
	 */
	public function getThemePath() {
		return trailingslashit(get_stylesheet_directory());
	}
	
	/**
	 * Returns the path to the wp-admin directory with a trailing slash.
	 * 
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
		return $this->root.'app/'.$path;
	}
	
	/**
	 * Returns the path to the user custom objects directory with a trailing slash.
	 * 
	 * @param string $path [Optional]
	 * 
	 * @return string
	 */
	public function getCustomObjectsPath($path = '') {
		return $this->getAppPath('custom/'.$path);
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
	
}
