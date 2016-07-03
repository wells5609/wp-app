<?php

namespace WordPress\Runtime;

use WordPress\Http\Request;

class Context implements \ArrayAccess
{
	
	const FRONT = 'front';
	const ADMIN = 'admin';
	const REST = 'rest';
	const AJAX = 'ajax';
	const CUSTOM = 'custom';
	
	protected $type;
	protected $request;
	protected $readyCallbacks = array();
	protected $methodCallbacks = array();
	protected $actionCallbacks = array();
	
	public function __construct(Request $request) {
		
		$this->request = $request;
		
		add_action('init', function() {
			global $wp_query;
			if (isset($wp_query->query_vars['rest_route'])) {
				$this->type = self::REST;
			} else if (is_admin()) {
				$this->type = self::ADMIN;
			} else if ('index.php' === $this->request->getScriptName()) {
				$this->type = self::FRONT;
			} else {
				$this->type = self::CUSTOM;
			}
		}, -PHP_INT_MAX);
		
		add_action('wp', function() {
			
			$method = $this->request->getMethod();
			$action = $this->request->getParam('action');
			
			if (isset($this->methodCallbacks[$method])) {
				$this->invokeCallbacks($this->methodCallbacks[$method]);
			}
			
			if ($action && isset($this->actionCallbacks[$action])) {
				$this->invokeCallbacks($this->actionCallbacks[$action]);
			}
			
			$this->invokeCallbacks($this->readyCallbacks);
			
		}, 0);
	}
	
	public function __call($func, array $args) {
		return call_user_func_array(array($this->request, $func), $args);
	}
	
	/**
	 * @return \WordPress\Request
	 */
	public function getRequest() {
		return $this->request;
	}
	
	public function ready($callable) {
		$this->readyCallbacks[] = $callable;
		return $this;
	}
	
	public function onMethod($method, $callable) {
		$method = strtoupper($method);
		if (! isset($this->methodCallbacks[$method])) {
			$this->methodCallbacks[$method] = array();
		}
		$this->methodCallbacks[$method][] = $callable;
		return $this;
	}
	
	public function onAction($action, $callable) {
		if (! isset($this->actionCallbacks[$action])) {
			$this->actionCallbacks[$action] = array();
		}
		$this->actionCallbacks[$action][] = $callable;
		return $this;
		
		if ('ready' === $tag) {
			return $this->ready($callable);
		}
		
		if (strpos($tag, ':') === false) {
			if (! isset($this->contextCallbacks[$tag])) {
				$this->contextCallbacks[$tag] = array();
			}
			$this->contextCallbacks[$tag][] = $callable;
		} else {
			list($param, $tag) = explode(':', $tag, 2);
			if (! isset($this->paramCallbacks[$param])) {
				$this->paramCallbacks[$param] = array();
			}
			$this->paramCallbacks[$param][$tag] = $callable;
		}
		
		return $this;
	}

	public function getMethod() {
		return $this->request->getMethod();
	}
	
	public function getScriptName() {
		return $this->request->getScriptName();
	}
	
	public function getUri() {
		return $this->request->getUri();
	}
	
	public function getQuery() {
		return $this->request->getQuery();
	}
	
	public function getParams($keys = null) {
		return $this->request->getParams($keys);
	}
	
	public function getParam($name, $default = null) {
		return $this->request->getParam($name, $default);
	}
	
	public function hasParam($name) {
		return $this->request->hasParam($name);
	}
	
	public function hasParams($keys) {
		return $this->request->hasParams($keys);
	}
	
	public function getHeaders() {
		return $this->request->getHeaders();
	}
	
	public function getHeader($name) {
		return $this->request->getHeader($name);
	}
	
	public function hasHeader($name) {
		return $this->request->hasHeader($name);
	}
	
	public function getServerParams() {
		return $this->request->getServerParams();
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function isFront() {
		return $this->type === self::FRONT;
	}
	
	public function isAdmin() {
		return $this->type === self::ADMIN;
	}
	
	public function isREST() {
		return $this->type === self::REST;
	}
	
	public function isApi() {
		return $this->isREST();
	}
	
	public function isAdminAjax() {
		return $this->request->getScriptName() === 'admin-ajax.php';
	}
	
	public function isXhr() {
		return $this->request->isXhr();
	}
	
	public function isGet() {
		return $this->request->isGet();
	}
	
	public function isPost() {
		return $this->request->isPost();
	}
	
	public function isPut() {
		return $this->request->isPut();
	}
	
	public function isHead() {
		return $this->request->isHead();
	}
	
	public function isDelete() {
		return $this->request->isDelete();
	}
	
	public function isOptions() {
		return $this->request->isOptions();
	}
	
	public function offsetGet($key) {
		return $this->request->offsetGet($key);
	}
	
	public function offsetExists($key) {
		return $this->request->offsetExists($key);
	}
	
	public function offsetSet($key, $value) {
		$this->request->offsetSet($key, $value);
	}
	
	public function offsetUnset($key) {
		$this->request->offsetUnset($key);
	}
	
	protected function invokeCallbacks(array $callbacks) {
		foreach($callbacks as $callback) {
			$callback($this);
		}
	}
	
}
