<?php

namespace WordPress\Post;

use WP_Post;

class Factory
{
	private $defaultClass = 'WordPress\\Post\\Post';
	private $classCallback;
	private $classes = array();
	
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
	
	public function setClass($post_type, $class) {
		$this->classes[$post_type] = $class;
	}
	
	public function getClass($post_type) {
			
		if (isset($this->classes[$post_type])) {
			return $this->classes[$post_type];
		}
		
		if (isset($this->classCallback)) {
			return call_user_func($this->classCallback, $post_type);
		}
		
		return $this->defaultClass;
	}
	
	public function getClasses() {
		return $this->classes;
	}
	
	public function create(WP_Post $post) {
		$class = $this->getClass($post->post_type);
		return new $class($post);
	}
	
}
