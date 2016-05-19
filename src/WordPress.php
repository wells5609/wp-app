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
	
	public static $autoloader;
	public static $di;
	public static $app;
	
	public static function init() {
		
		foreach(spl_autoload_functions() as $callable) {
			if (is_array($callable) && is_object($callable[0])) {
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
	
	public static function load(WordPress\DI $di = null) {
		
		if (! isset($di)) {
			$di = new WordPress\Di\FactoryDefault;
		}
		
		$di->setShared('autoloader', self::$autoloader);
		
		self::$di = $di;
		
		if ($di->has('app')) {
			$app = $di->get('app');
		} else {
			$di->setShared('app', $app = new WordPress\App($di));
		}
		
		if (is_blog_installed()) {
			WordPress\Plugin\MustUsePlugins::load(self::$autoloader);
		}
		
		// Register default theme directory
		if (! defined('WP_DEFAULT_THEME')) {
			register_theme_directory(ABSPATH.'wp-content/themes');
		}
		
		// Disallow site indexing on non-production environments
		if ($app->isDev() && ! $app->isAdmin()) {
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
