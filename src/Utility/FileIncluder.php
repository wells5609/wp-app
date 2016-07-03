<?php

namespace WordPress\Utility;

use InvalidArgumentException;

class FileIncluder
{
	
	private $keyword = 'require';
	private $function = 'WordPress\Utility\_require';
	private $callback;
	private $variables = array();
	
	public function __construct($callback = null) {
		if ($callback) {
			$this->setCallback($callback);
		} else {
			$this->resetCallback();
		}
	}
	
	public function setCallback($callback) {
		$this->callback = $callback;
	}
	
	public function resetCallback() {
		$this->callback = function ($result) {
			return $result;
		};
	}
	
	public function setKeyword($keyword) {
		switch($keyword) {
			case 'require':
			case 'require_once':
			case 'include':
			case 'include_once':
				$this->keyword = $keyword;
				break;
			default:
				throw new InvalidArgumentException("Invalid include keyword: '$keyword'");
		}
		$this->function = __NAMESPACE__."\\_$this->keyword";
	}
	
	public function getKeyword() {
		return $this->keyword;
	}
	
	public function with($varname, $value) {
		$this->variables[$varname] = $value;
	}
	
	public function withVars(array $variables) {
		$this->variables = $variables;
	}
	
	public function __invoke($file) {
		$function = $this->function;
		$callback = $this->callback;
		return $callback($function($file, $this->variables));
	}
	
	public function load($file) {
		return $this($file);
	}
	
	public function loadDirectory($directory, $file_extension = '.php') {
		$results = array();
		foreach(glob(rtrim($directory, '/\\')."/*$file_extension") as $file) {
			$results[$file] = $this($file);
		}
		return $results;
	}
	
}

/**
 * Loads a file using the 'require' keyword.
 * 
 * @param string $__FILE__
 * @return mixed
 */
function _require($__FILE__, array &$variables = array()) {
	$variables and \extract($variables, EXTR_REFS);
	unset($variables);
	return require $__FILE__;
}

/**
 * Loads a file using the 'require_once' keyword.
 * 
 * @param string $__FILE__
 * @return mixed
 */
function _require_once($__FILE__, array &$variables = array()) {
	$variables and \extract($variables, EXTR_REFS);
	unset($variables);
	return require_once $__FILE__;
}

/**
 * Loads a file using the 'include' keyword.
 * 
 * @param string $__FILE__
 * @return mixed
 */
function _include($__FILE__, array &$variables = array()) {
	$variables and \extract($variables, EXTR_REFS);
	unset($variables);
	return include $__FILE__;
}

/**
 * Loads a file using the 'include_once' keyword.
 * 
 * @param string $__file
 * @return mixed
 */
function _include_once($__FILE__, array &$variables = array()) {
	$variables and \extract($variables, EXTR_REFS);
	unset($variables);
	return include_once $__FILE__;
}
