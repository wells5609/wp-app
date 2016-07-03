<?php

namespace WordPress\Events\Ajax;

use Closure;
use RuntimeException;

class LazyClassAction extends Action
{
	
	protected $class;
	protected $method;
	
	/**
	 * Constructor override.
	 * 
	 * @param string $action
	 * @param callable $callback [Optional]
	 */
	public function __construct($action, $classMethod = null, $priority = 10, $num_args = 1) {
		$this->action = $action;
		if ($classMethod) {
			if (is_array($classMethod)) {
				$this->setClassMethodArray($classMethod);
			} else if (! is_string($classMethod)) {
				throw new \InvalidArgumentException(
					"classMethod must be array or string, given; ".gettype($classMethod)
				);
			}
			$this->setClass($callback);
		}
		$this->priority = $priority;
		$this->numberArguments = $num_args;
	}
	
	public function setClassMethodArray(array $classMethod) {
		if (is_array($classMethod)) {
			list($this->class, $this->method) = $classMethod;
		}
	}
	
	public function setClass($class) {
		$this->class = $class;
	}
	
	public function setMethod($method) {
		$this->method = $method;
	}
	
	public function init() {
		
		if (empty($this->callback)) {
			throw new RuntimeException("Missing action callback for '$this->action'");
		}
		
		add_action(
			'wp_ajax_nopriv_'.$this->action, 
			array($this, '_doActionNoPriv'), 
			$this->priority, 
			$this->numberArguments
		);
		
		add_action(
			'wp_ajax_'.$this->action, 
			array($this, '_doAction'), 
			$this->priority, 
			$this->numberArguments
		);
	}
	
	public function _doAction() {
		$callback = array();
		return call_user_func_array($this->callback, func_get_args());
	}
	
	public function _doActionNoPriv() {
		$callback = $this->noPrivCallback ?: $this->callback;
		return call_user_func_array($callback, func_get_args());
	}
	
}
