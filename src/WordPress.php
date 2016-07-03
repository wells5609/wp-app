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
	 * @var WordPress\App
	 */
	private static $app;
	
	public static function init(WordPress\App $app) {
		
		// Find the Composer ClassLoader instance
		foreach(spl_autoload_functions() as $callable) {
			if (is_array($callable)) {
				if ($callable[0] instanceof Composer\Autoload\ClassLoader) {
					$app->setShared('autoloader', $callable[0]);
					break;
				}
			}
		}
		
		$app->setShared('app', $app);
		
		self::$app = $app;
	}
	
	public static function app() {
		return self::$app;
	}
	
	public static function autoloader() {
		return self::$app->get('autoloader');
	}
	
	public static function get($key) {
		return self::$app->get($key);
	}
	
	public static function set($key, $value, $shared = true) {
		self::$app->set($key, $value, $shared);
	}
	
	public static function has($key) {
		return self::$app->has($key);
	}
	
	public static function __callStatic($func, array $args) {
		
		if (empty($args)) {
			return self::$app->get($func);
		}
		
		array_unshift($args, $func);
		
		return call_user_func_array(array(self::$app, 'get'), $args);
	}
	
}
