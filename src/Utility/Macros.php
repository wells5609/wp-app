<?php

namespace WordPress\Utility;

class Macros
{
	
	protected static $macros = [];
	
	public static function set($name, callable $callback) {
		static::$macros[$name] = $callback;
	}
	
	public static function get($name) {
		return isset(static::$macros[$name]) ? static::$macros[$name] : null;
	}
	
	public static function exists($name) {
		return isset(static::$macros[$name]);
	}
	
	public static function delete($name) {
		unset(static::$macros[$name]);
	}
	
	public static function __callStatic($func, array $args) {
		if (isset(static::$macros[$func])) {
			return call_user_func_array(static::$macros[$func], $args);
		}
	}
	
	public function __set($var, $value) {
		$this::$macros[$var] = $value;
	}
	
	public function __get($var) {
		return isset($this::$macros[$var]) ? $this::$macros[$var] : null;
	}
	
	public function __isset($var) {
		return isset($this::$macros[$var]);
	}
	
	public function __unset($var) {
		unset($this::$macros[$var]);
	}
	
	public function __call($func, array $args) {
		if (isset($this::$macros[$func])) {
			return call_user_func_array($this::$macros[$func], $args);
		}
	}
	
}
