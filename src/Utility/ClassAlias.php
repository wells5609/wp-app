<?php
/**
 * @package WordPress
 * @subpackage Utility
 */

namespace WordPress\Utility;

/**
 * Creates and lazily resolves class aliases at runtime.
 */
class ClassAlias
{
	
	/**
	 * Array of 'alias' => 'classname'.
	 * 
	 * @var array
	 */
	protected static $aliases = [];
	
	/**
	 * Resolved classes.
	 * 
	 * @var array
	 */
	public static $resolved = [];
	
	/**
	 * Whether registered with spl_autoload_register().
	 * 
	 * @var boolean
	 */
	protected static $registered = false;
	
	/**
	 * Add a class alias.
	 * 
	 * Same format as class_alias().
	 * 
	 * Aliaser is registered with SPL the first time called.
	 * 
	 * @param string $original Original, fully resolved class.
	 * @param string $alias Alias to assign to class.
	 * @return void
	 */
	public static function add($classname, $alias) {
		static::$aliases[$alias] = $classname;
		if (! static::$registered) {
			static::register();
		}
	}
	
	/**
	 * Adds array of class aliases.
	 * 
	 * @param array $aliases Key-value pairs of $class => $alias
	 * @return void
	 */
	public static function addArray(array $aliases) {
		foreach ($aliases as $class => $alias) {
			static::$aliases[$alias] = $class;
		}
	}
	
	/**
	 * Returns the alias for a class.
	 * 
	 * @param string $class Fully resolved class name.
	 * @return string|false Alias if exists, otherwise false.
	 */
	public static function getAlias($class) {
		return array_search($class, static::$aliases, true);
	}
	
	/**
	 * Returns true if given class has an alias.
	 * 
	 * @param string $class Fully resolved class name.
	 * @return boolean True if class has alias registered, otherwise false.
	 */
	public static function hasAlias($class) {
		return in_array($class, static::$aliases, true);
	}
	
	/**
	 * Returns true if given class name is an alias.
	 * 
	 * @param string $class Class to check if alias.
	 * @return boolean True if given class is alias, otherwise false.
	 */
	public static function isAlias($class) {
		return isset(static::$aliases[$class]);
	}
	
	/**
	 * Returns a fully resolved class name, given an alias.
	 * 
	 * @param string $alias An alias to resolve.
	 * @return string|null Resolved class name, or null on failure.
	 */
	public static function resolve($alias) {
		return isset(static::$aliases[$alias]) ? static::$aliases[$alias] : null;
	}
	
	/**
	 * Register as SPL autoloader.
	 * 
	 * @return void
	 */
	public static function register() {
		spl_autoload_register(get_called_class().'::load', true, true);
		static::$registered = true;
	}
	
	/**
	 * Unregister the SPL autoloader.
	 * 
	 * @return void
	 */
	public static function unregister() {
		spl_autoload_unregister(get_called_class().'::load');
		static::$registered = false;
	}
	
	/**
	 * Returns true if registered as SPL autoloader, otherwise false.
	 * 
	 * @return boolean
	 */
	public static function isRegistered() {
		return static::$registered;
	}
	
	/**
	 * SPL autoload callback to lazily declare class aliases.
	 * 
	 * @param string $alias Class alias
	 * @return void
	 */
	public static function load($alias) {
		
		if (isset(static::$aliases[$alias])) {
				
			$class = static::$aliases[$alias];
			
			if (class_exists($class, true)) {
				
				class_alias($class, $alias);
				
				static::$resolved[$class] = $alias;
			}
		}
	}
	
}