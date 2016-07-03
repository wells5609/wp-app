<?php

/**
 * Returns the App instance.
 * @return \WordPress\App
 */
function app() {
	return WordPress\App::instance();
}

/**
 * Returns or sets a DI value.
 * 
 * @param string $key
 * @param mixed $value [Optional]
 * @param boolean $shared [Optional] Default = false
 * 
 * @return mixed|void
 */
function di($key, $value = null, $shared = false) {
	if ($value === null) {
		return WordPress\App::instance()->get($key);
	} else {
		WordPress\App::instance()->set($key, $value, $shared);
	}
}

/**
 * Returns the 'app' path with a trailing slash.
 * 
 * @param string $path [Optional]
 * @return string
 */
function app_path($path = '') {
	return APPROOT.'/'.$path;
}

function wp_active_filter() {
	global $wp_current_filter;
	if (! empty($wp_current_filter)) {
		return end($wp_current_filter);
	}
	trigger_error(__FUNCTION__.' may only be called from within WordPress filters or actions.');
}

function event($tag = null) {
	return $tag ? WordPress\Event::get($tag) : WordPress\Event::current();
}

function stop_propagation() {
	event()->stopPropagation();
}

function prevent_default() {
	event()->preventDefault();
}

function is_default_prevented() {
	return event()->isDefaultPrevented();
}

/**
 * Returns the public properties of the given object.
 *
 * @param object $object
 * 		An object instance.
 *
 * @return array
 * 		An associative array of public non-static properties for the specified object.
 * 		If a property has not been assigned a value, it will be returned with a null value
 */
function get_object_public_vars($object) {
	return get_object_vars($object);
}

/**
 * Locates and returns the SPL-registered Composer autoloader.
 *
 * @return Composer\Autoload\ClassLoader
 */
function composer_autoloader() {
	static $autoloader;
	if (! isset($autoloader)) {
		foreach(spl_autoload_functions() as $callable) {
			if (is_array($callable)) {
				if ($callable[0] instanceof Composer\Autoload\ClassLoader) {
					$autoloader = $callable[0];
					break;
				}
			}
		}
	}
	return $autoloader;
}

/**
 * Returns the relative path to $path from $base. Both paths must exist.
 *
 * @param string $base
 * @param string $path
 *
 * @return string
 */
function getRelativePath($base, $path) {
	return substr(realpath($path), strlen(realpath($base)));
}

function includePhpFiles($dir) {
	foreach(glob(rtrim($dir, '/\\').'/*.php') as $__file) {
		require $__file;
	}
}

function array_keys_exist(array $keys, array $search) {
	foreach($keys as $key) {
		if (! array_key_exists($key, $search)) {
			return false;
		}
	}
	return true;
}

function array_pull(array $array, $keys) {
	if (! is_array($keys)) {
		return array_key_exists($keys, $array) ? $array[$keys] : null;
	}
	$result = array();
	foreach($keys as $key) {
		if (array_key_exists($key, $array)) {
			$result[$key] = $array[$key];
		}
	}
	return $result;
}

function wp_admin_ui_notice($content, $type = 'info', $dismiss = true, $alt = false) {
	return WordPress\Admin\UI::notice($content, $type, $dismiss, $alt);
}

/**
 * Returns a registered data repository.
 *
 * @return \WordPress\Data\RepositoryInterface
 */
function get_repository($name) {
	return WordPress::get('dataManager')->getRepository($name);
}

/**
 * Returns or prints debug statistics.
 * 
 * @param boolean $display [Optional] Default = false
 * @return string
 */
function wp_debug($display = false) {
	$queries = get_num_queries().' DB queries';
	$memory = number_format(memory_get_peak_usage()/1024/1024, 3).' MB memory';
	$timer = timer_stop(false, 4)*1000 .' ms';
	$string = '<span id="wp-debug-output"> '.$queries.' // '.$memory.' // '.$timer.'</span>';
	return $display ? print $string : $string;
}

function wp_debug_print() {
	echo wp_debug();
}
