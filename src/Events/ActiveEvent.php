<?php

namespace WordPress\Events;

use RuntimeException;

class ActiveEvent
{
	
	protected static $preventDefault = array();
	
	protected $name;
	protected $filters;
	
	public static function get() {
		return new static($GLOBALS['wp_current_filter']);
	}
	
	public function __construct(array $filters) {
		
		if (empty($filters)) {
			throw new RuntimeException("No current filter or action");
		}
		
		$this->filters = $filters;
		$this->name = end($filters);
		
		if (! isset(static::$preventDefault[$this->name])) {
			static::$preventDefault[$this->name] = false;
		}
	}
	
	public function getName() {
		return $this->name;
	}

	public function preventDefault() {
		static::$preventDefault[$this->name] = true;
		return $this;
	}
	
	public function isDefaultPrevented() {
		return static::$preventDefault[$this->name];
	}

	public function stopPropagation() {
		remove_all_filters($this->name);
		return $this;
	}
	
	public function hasParents() {
		return count($this->filters) > 1;
	}
	
	public function getParents() {
		if ($this->hasParents()) {
			$filters = $this->filters;
			array_pop($filters);
			return $filters;
		}
	}
	
	public function isParent($name) {
		return $this->hasParents() && in_array($name, $this->getParents(), true);
	}
	
	public function isIn($names) {
		is_array($names) or $names = array($names);
		foreach($names as $name) {
			if ($name === $this->name || in_array($name, $this->filters, true)) {
				return true;
			}
		}
		return false;
	}
	
}
