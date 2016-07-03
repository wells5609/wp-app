<?php

namespace WordPress\Http\WebService;

use WP_Error;
use WordPress\Http\Request;

class Adapter
{

	protected $url;
	protected $request;
	protected $lastRequest = array();
	protected $methods = array();

	protected static $instances = array();

	public static function register($id, $baseUrl, array $methods) {
		$class = apply_filters("{$id}_api_adapter_class", __CLASS__);
		$adapter = self::$instances[$id] = new $class($baseUrl, $methods);
		return $adapter;
	}

	public static function get($id) {
		return isset(self::$instances[$id]) ? self::$instances[$id] : null;
	}

	public function __construct($baseUrl, array $methods) {
		$this->url = rtrim($baseUrl, '/');
		$this->resetRequest();
		$this->buildMethods($methods);
	}

	public function resetRequest() {
		$this->request = new Request;
		$this->lastRequest = array();
	}

	public function setOption($name, $value) {
		$this->request->$name = $value;
		return $this;
	}

	public function getOption($name) {
		return $this->request->$name;
	}

	public function call($method, $path = '', $params = array(), $http_method = null) {

		if (! isset($this->methods[$method])) {
			return false;
		}

		$def = $this->methods[$method];

		if (! empty($http_method) && ! in_array($http_method, $def->http_methods)) {
			return new WP_Error('invalid_http_method', "HTTP method '$http_method' not allowed for method '$def->name'.");
		}

		$method_string = $def->build_url($path, $params);

		if (is_wp_error($method_string)) {
			return $method_string;
		}

		$url = trailingslashit($this->baseurl).$method_string;

		if (empty($http_method) && ! empty($def->http_methods)) {
			$_methods = $def->http_methods;
			$http_method = array_shift($_methods);
		} else {
			$http_method = 'GET';
		}

		$this->last_request['method'] = $method;
		$this->last_request['http_method'] = $http_method;
		$this->last_request['url'] = $url;

		return $this->request->send_request($url, $http_method);
	}

	protected function buildMethods(array $methods) {

		foreach ($methods as $method => $args) {

			$adapterMethod = new Method($method);

			if (! empty($args['params'])) {
				$adapterMethod->setParams($args['params']);
			}

			if (! isset($args['paths'])) {
				$args['paths'] = false;
			}

			$adapterMethod->setPaths($args['paths']);

			if (! isset($args['method'])) {
				$args['method'] = array('GET');
			}

			$adapterMethod->setHttpMethods((array)$args['method']);
			
			$this->methods[$method] = $adapterMethod;
		}
	}

	public function getLastRequest() {
		return empty($this->lastRequest) ? null : $this->lastRequest;
	}

	public function __call($func, $params) {
		if (isset($this->methods[$func])) {
			return $this->call($func, isset($params[0]) ? $params[0] : '', isset($params[1]) ? $params[1] : array(), isset($params[2]) ? $params[2] : null);
		}
	}

}

class Method
{
	
	const ANY		= 'any';
	const STRING	= 'string';
	const BOOL		= 'bool';
	const NUMBER	= 'number';
	const ENUM		= 'enum';
	
	protected $name;
	protected $httpMethods = array();
	protected $params = array();
	protected $paramDefaults = array();
	protected $requiredParams = array();
	protected $paramOptions = array();

	// if false, paths not allowed. anything else paths ok
	protected $paths = false;

	// if true, $path must not be empty in buildUrl()
	protected $pathRequired = false;

	public function __construct($name) {
		$this->name = $name;
	}
	
	public function setHttpMethods(array $methods) {
		$this->httpMethods = array_map('strtoupper', $methods);
	}

	public function setParams(array $params) {
		foreach ($params as $param => $type) {
			$this->setParam($param, $type);
		}
	}

	public function setParam($name, $value) {

		if ($name[0] === '*') {
			$name = ltrim($name, '*');
			$this->requiredParams[] = $name;
		}
		
		if (is_null($value)) {
			$this->params[$name] = self::ANY;
			$this->paramDefaults[$name] = null;
		
		} else if (is_bool($value)) {
			$this->params[$name] = self::BOOL;
			$this->paramDefaults[$name] = $value;
		
		} else if (is_numeric($value)) {
			$this->params[$name] = self::NUMBER;
			$this->paramDefaults[$name] = $value;
			
		} else if (is_string($value)) {
			$this->params[$name] = self::STRING;
			$this->paramDefaults[$name] = $value;
		
		} else if (is_array($value)) {
			$this->params[$name] = self::ENUM;
			foreach($value as &$enum) {
				if ($enum[0] === '*') {
					$enum = ltrim($enum, '*');
					$this->paramDefaults[$name] = $enum;
					break;
				}
			}
			$this->paramOptions[$name] = $value;
		}
	}

	public function setPaths($value) {
		$this->paths = $value;
		if ('*' === $value) {
			$this->pathRequired = true;
		}
	}

	public function buildUrl($path = '', $params = array()) {

		if (! $path && $this->pathRequired) {
			throw new RuntimeException("Missing required method path for '$this->name'");
		}
		
		$this->validateRequiredParameters($params);
		
		$url = $this->name;

		if ($path && $this->paths) {
			$url .= '/'.trim($path, '/');
		}

		if ($params) {
			
			$url .= '?';
			
			foreach ($params as $param => $value) {
				
				if (! $this->isParamValid($param, $value)) {
					throw new RuntimeException("Invalid parameter '$param' value ('$value') for method '$this->name'");
				}
				
				$url .= urlencode($param).'='.urlencode($value).'&';
			}
		}

		return rtrim($url, '&');
	}

	public function isParamValid($param, $value) {

		if (! isset($this->params[$param])) {
			return false;
		}

		switch ($this->params[$param]) {
			case self::ANY:
				return true;
			case self::BOOL:
				return is_bool($value);
			case self::NUMBER:
				return is_numeric($value);
			case self::STRING:
				return is_string($value);
			case self::ENUM:
				if (! isset($this->paramOptions[$param])) {
					return false;
				}
				return in_array($value, $this->paramOptions[$param]);
			default: 
				break;
		}
		
		return false;
	}
	
	protected function validateRequiredParameters(array &$params) {
		foreach ($this->requiredParams as $param) {
			if (! isset($params[$param])) {
				if (isset($this->paramDefaults[$param])) {
					$params[$param] = $this->paramDefaults[$param];
				} else {
					throw new RuntimeException("Missing required parameter '$param' for method '$this->name'");
				}
			}
		}
	}

}
