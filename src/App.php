<?php

namespace WordPress;

use ArrayAccess;
use RuntimeException;

class App implements ArrayAccess
{

	/**
	 * The App instance.
	 * 
	 * @var \WordPress\App
	 */
	protected static $instance;
	
	/**
	 * The DI instance.
	 * 
	 * @var \WordPress\DI
	 */
	protected static $di;
	
	/**
	 * The current custom post instance.
	 * 
	 * @var \WordPress\Post\Post
	 */
	protected static $post;
	
	/**
	 * Additional variables to make available in template files.
	 * 
	 * @var array
	 */
	protected $extraTemplateVars = array();
	
	/**
	 * Path to the root installation directory with a trailing slash.
	 * 
	 * @var string
	 */
	protected $root;
	
	/**
	 * Creates the App instance.
	 * 
	 * @param \WordPress\DI $di
	 * 
	 * @throws \RuntimeException if an App has already been created
	 */
	public function __construct(DI $di) {
		
		if (isset(static::$instance)) {
			throw new RuntimeException('Cannot recreate App instance.');
		}
		
		if (! isset($GLOBALS['root_dir'])) {
			throw new RuntimeException("Missing 'root_dir' global variable.");
		}
		
		static::$di = $di;
		static::$instance = $this;
		
		$this->root = trailingslashit($GLOBALS['root_dir']);
		
		add_action('init', function() {
			$this->get('customTypes')->load();
		}, 100);
		
		add_action('app.init', array($this, 'init'), PHP_INT_MAX);
		
		add_action('plugins_loaded', function() {
			 do_action('app.init', $this);
		}, PHP_INT_MAX);
	}
	
	public function init() {
		$this->loadFile($this->getAppPath('init.php'));
	}
	
	/**
	 * Returns the App object.
	 * 
	 * @return \WordPress\App
	 */
	public static function instance() {
		return static::$instance;
	}
	
	/**
	 * Returns the DI instance.
	 * 
	 * @return \WordPress\DI
	 */
	public static function di() {
		return static::$di;
	}
	
	/**
	 * Resolves a value from the DI by name.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public static function get($key) {
		return static::$di->get($key);
	}
	
	/**
	 * Sets a value in the DI.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param boolean $shared [Optional] Default = true
	 */
	public static function set($key, $value, $shared = true) {
		static::$di->set($key, $value, $shared);
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
	public function env($name) {
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
	
	/**
	 * Whether WP_ENV === "development"
	 * 
	 * @return boolean
	 */
	public function isDev() {
		return WP_ENV === 'development';
	}
	
	/**
	 * Loads a file with the App in scope.
	 * 
	 * 3 variables are made available in the file:
	 *   $this  The WordPress\App instance
	 *   $app   "   "
	 *   $di    The WordPress\DI object
	 * 
	 * @param string $__file
	 * @param bool $strict [Optional] Default = false
	 * 
	 * @return mixed Value returned from file, if any
	 */
	public function loadFile($__file, $strict = false) {
		if (file_exists($__file)) {
			$app = $this;
			$di = $this->di();
			return require $__file;
		}
		if ($strict) {
			throw new RuntimeException("Cannot load non-existant file: '$__file'");
		}
	}
	
	/**
	 * Returns the current custom post object.
	 * 
	 * @return \WordPress\Post\Post
	 */
	public function getPost() {
		if (! isset(static::$post) || static::$post->ID != $GLOBALS['post']->ID) {
			static::$post = static::$di->get('postFactory')->create($GLOBALS['post']);
		}
		return static::$post;
	}
	
	/**
	 * Returns the autoloader object.
	 * 
	 * @return \Xpl\ClassLoader
	 */
	public function getAutoloader() {
		return static::$di->get('autoloader');
	}
	
	/**
	 * Returns the environment object.
	 * 
	 * @return \WordPress\Env
	 */
	public function getEnv() {
		return static::$di->get('env');
	}
	
	/**
	 * Returns the active theme object.
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function getTheme() {
		return static::$di->get('theme');
	}
	
	/**
	 * Sets a custom template variable that will be available in theme template files.
	 * 
	 * @param string $varname
	 * @param mixed $value
	 */
	public function setTemplateVar($varname, $value) {
		$this->extraTemplateVars[$varname] = $value;
	}
	
	/**
	 * Returns the array of custom template variables.
	 * 
	 * @return array
	 */
	public function getTemplateVars() {
		return $this->extraTemplateVars;
	}
	
	/**
	 * Checks whether there are custom template variables set.
	 * 
	 * @return boolean
	 */
	public function hasTemplateVars() {
		return ! empty($this->extraTemplateVars);
	}
	
	/* --------------------------------------------------------
	 * Implements ArrayAccess
	 * ----------------------------------------------------- */
	
	/**
	 * Resolves a DI service or template var by key.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function offsetGet($key) {
		if (static::$di->has($key)) {
			return static::$di->get($key);
		}
		if (isset($this->extraTemplateVars[$key])) {
			return $this->extraTemplateVars[$key];
		}
		return null;
	}
	
	/**
	 * Registers a shared DI service.
	 * 
	 * @param string $key Dependency key.
	 * @param mixed $value
	 */
	public function offsetSet($key, $value) {
		static::$di->setShared($key, $value);
	}
	
	/**
	 * Checks whether a DI service or template var with the given key exists.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function offsetExists($key) {
		return static::$di->has($key) || isset($this->extraTemplateVars[$key]);
	}
	
	/**
	 * Deletes a DI service or template var by key.
	 * 
	 * @param string $key
	 */
	public function offsetUnset($key) {
		if (static::$di->has($key)) {
			static::$di->remove($key);
		} else {
			unset($this->extraTemplateVars[$key]);
		}
	}
	
}