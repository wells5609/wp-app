<?php

namespace WordPress\Http\RequestListener;

abstract class AbstractListener
{
	
	/**
	 * @var string
	 */
	protected $methods = 'GET POST HEAD PUT DELETE PATCH OPTIONS';
	
	/**
	 * @var callable[]
	 */
	protected $callbacks = array();
	
	/**
	 * Whether the listener is active for the current request.
	 * 
	 * @return boolean
	 */
	abstract protected function isActive();
	
	/**
	 * Returns the current value.
	 * 
	 * @return string
	 */
	abstract protected function getValue();
	
	/**
	 * Sets the HTTP method(s).
	 * 
	 * @param string|array $http_method
	 * @return \WordPress\Http\RequestListener\AbstractListener
	 */
	public function via($http_method) {
		if (is_array($http_method)) {
			$http_method = implode(' ', $http_method);
		}
		$this->methods = strtoupper($http_method);
		return $this;
	}
	
	/**
	 * Adds a callback to run on requests with the given value.
	 * 
	 * @param string $value
	 * @param callable $callback
	 * @return \WordPress\Http\RequestListener\AbstractListener
	 */
	public function on($value, $callback) {
		$this->callbacks[$value][] = $callback;
		return $this;
	}

	/**
	 * 'parse_request' action callback. Should not be called directly.
	 *
	 * @return void
	 */
	public function _run() {
		if ($this->isActiveHttpMethod() && $this->isActive()) {
			$this->invoke($this->getValue());
		}
	}
	
	/**
	 * Registers the run() method on the 'parse_request' action.
	 * 
	 * @return void
	 */
	protected function init() {
		add_action('parse_request', array($this, '_run'));
	}
	
	/**
	 * Returns the current HTTP method.
	 * 
	 * @return string
	 */
	protected function getHttpMethod() {
		return $_SERVER['REQUEST_METHOD'];
	}
	
	/**
	 * Checks whether we are listening on the current HTTP method.
	 * 
	 * @return boolean
	 */
	protected function isActiveHttpMethod() {
		return strpos($this->methods, $this->getHttpMethod()) !== false;
	}
	
	/**
	 * Invokes callbacks listening for the given value.
	 * 
	 * @param string $value
	 * @return void
	 */
	protected function invoke($value) {
		if (isset($this->callbacks[$value])) {
			foreach($this->callbacks[$value] as $callback) {
				$callback();
			}
		}
	}
	
}
