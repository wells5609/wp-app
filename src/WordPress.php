<?php

/**
 * Represents the WordPress software environment.
 *
 * @since 1.0
 * @author wells5609
 * @license MIT
 * @package wells5609/wp-app
 */
class WordPress
{
	
	/**
	 * @var Composer\Autoload\ClassLoader
	 */
	public static $autoloader;
	
	/**
	 * @var WordPress\DI
	 */
	public static $di;
	
	/**
	 * @var WordPress\App
	 */
	public static $app;
	
	/**
	 * Initialize the kernel
	 * 
	 * This method MUST be called at the end of your "config/application.php" file.
	 * 
	 * 1. Locate the Composer class loader or create one if it does not exist
	 * 2. Add the 'WordPress' namespace to autoload classes from the current directory (a la PSR-4)
	 */
	public static function init() {
		
		foreach(spl_autoload_functions() as $callable) {
			if (is_array($callable)) {
				if ($callable[0] instanceof Composer\Autoload\ClassLoader) {
					self::$autoloader = $callable[0];
					break;
				}
			}
		}
		
		if (! isset(self::$autoloader)) {
			self::$autoloader = new Composer\Autoload\ClassLoader();
			self::$autoloader->register();
		}
		
		self::$autoloader->addPsr4('WordPress\\', array(__DIR__));
	}
	
	/**
	 * Loads the kernel
	 * 
	 * 1. Creates the WordPress\DI instance of self::$di if one has not been set
	 * 2. Adds self::$autoloader to the DI as 'autoloader'
	 * 3. Creates the WordPress\App instance of self::$app if 'app' does not exist in the DI
	 * 4. Loads must-use plugins in folders (@Roots/Bedrock)
	 * 5. Register the theme directory (@Roots/Bedrock)
	 * 6. Disallow site indexing if non-production (@Roots/Bedrock)
	 */
	public static function load() {
		
		if (! isset(self::$di)) {
			self::$di = new WordPress\Di\FactoryDefault;
		}
		
		$di = self::$di;
		
		$di->setShared('autoloader', self::$autoloader);
		
		if (! $di->has('app')) {
			$di->setShared('app', new WordPress\App($di));
		}
		
		self::$app = $di->get('app');
		
		if (is_blog_installed()) {
			WordPress\Plugin\MustUsePlugins::load(self::$autoloader);
		}
		
		// Register default theme directory
		if (! defined('WP_DEFAULT_THEME')) {
			register_theme_directory(ABSPATH.'wp-content/themes');
		}
		
		// Disallow site indexing on non-production environments
		if (WP_ENV !== 'production' && ! is_admin()) {
			add_action('pre_option_blog_public', '__return_zero');
		}
	}
	
	public static function autoloader() {
		return self::$autoloader;
	}
	
	public static function di() {
		return self::$di;
	}
	
	public static function app() {
		return self::$app;
	}
	
}
