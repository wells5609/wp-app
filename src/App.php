<?php
/**
 * @package Core
 */
namespace WordPress;

use WordPress\Application\Action;

/**
 * App serves as a service locator for your application components.
 * 
 * @author wells
 * @since 1.0
 * @version 1.1
 */
class App extends DependencyInjection\Container
{
	
	/**
	 * @var \WordPress\App
	 */
	protected static $instance;
	
	/**
	 * @var \Composer\Autoload\ClassLoader
	 */
	protected $autoloader;

	/**
	 * @var \WordPress\Application\Environment
	 */
	protected $environment;

	/**
	 * @var \WordPress\Application\Action[]
	 */
	protected $events;
	
	/**
	 * Constructor.
	 */
	public function __construct(Application\Environment $env) {
		if (! isset(static::$instance)) {
			static::$instance = $this;
		}
		$this->environment = $env;
		$this->autoloader = function_exists('composer_autoloader') ? \composer_autoloader() : null;
		$this->events = array(
			(new Action('app.preload'))	->bind('muplugins_loaded',	PHP_INT_MAX),
			(new Action('app.load'))	->bind('plugins_loaded',	PHP_INT_MAX),
			(new Action('app.init'))	->bind('init',				-PHP_INT_MAX),
			(new Action('app.loaded'))	->bind('wp_loaded',			PHP_INT_MAX),
			(new Action('app.request'))	->bind('parse_request',		-PHP_INT_MAX),
			(new Action('app.ready'))	->bind('wp',				PHP_INT_MAX)
		);
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
	 * Adds a callback to an application event.
	 * 
	 * @param string $event
	 * @param callable $callback
	 * @param int $priority [Optional] Default = 0
	 * @param int $num_args [Optional] Default = 1
	 */
	public static function on($event, $callback, $priority = 0, $num_args = 1) {
		add_action('app.'.$event, $callback, $priority, $num_args);
	}

	/**
	 * Loads a file with the App in scope as `$app`.
	 *
	 * @param string $__file
	 *
	 * @return mixed Value returned from file, if any
	 */
	public static function loadFile($__file) {
		if (file_exists($__file)) {
			$app = static::$instance;
			return require $__file;
		}
	}
	
	/**
	 * Returns the global autoloader.
	 * 
	 * @return \Composer\Autoload\ClassLoader
	 */
	public function getAutoloader() {
		return $this->autoloader;
	}
	
	/**
	 * Returns the application environment.
	 * 
	 * @return \WordPress\Application\Environment
	 */
	public function getEnv() {
		return $this->environment;
	}

	public function getPost() {
		return Model\Post\Post::instance();
	}
	
	/**
	 * Returns the autoloader object.
	 *
	 * @return \Composer\Autoload\ClassLoader
	 */
	public static function autoloader() {
		return static::instance()->getAutoloader();
	}
	
	/**
	 * Returns the environment object.
	 * 
	 * @return \WordPress\Application\Environment
	 */
	public static function env() {
		return static::instance()->getEnv();
	}

	/**
	 * Returns the current custom post object.
	 *
	 * @return \WordPress\Model\Post\Post
	 */
	public static function post() {
		return static::instance()->getPost();
	}
	
	/**
	 * Allows DI services to be resolved by static method call.
	 * 
	 * @param string $func
	 * @param array $args
	 * 
	 * @return mixed
	 */
	public static function __callStatic($func, array $args) {
		return static::$instance->get($func, empty($args) ? null : $args);
	}
	
}