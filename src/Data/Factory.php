<?php

namespace WordPress\Data;

abstract class Factory
{
	
	protected $defaultClass;
	protected $classCallback;
	protected $classes = array();
	
	public function setDefaultClass($class) {
		$this->defaultClass = $class;
	}
	
	public function getDefaultClass() {
		return $this->defaultClass;
	}
	
	public function setClassCallback($callback) {
		$this->classCallback = $callback;
	}
	
	public function getClassCallback() {
		return $this->classCallback;
	}
	
	public function setClass($type, $class) {
		$this->classes[$type] = $class;
	}
	
	public function getClass($type) {	
		if (isset($this->classes[$type])) {
			return $this->classes[$type];
		}
		if (isset($this->classCallback)) {
			return call_user_func($this->classCallback, $type);
		}
		return $this->defaultClass;
	}
	
	public function getClasses() {
		return $this->classes;
	}
	
	public function __invoke($data = null, $class = null) {
		if (! $class) {
			$class = $this->getDefaultClass();
		}
		return new $class($data);
	}
	
}
