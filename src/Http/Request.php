<?php

namespace WordPress\Http;

class Request
{
	
	const METHOD_OVERRIDE_PARAM = '_method';
	const FORMAT_PARAM = 'format';
	
	protected static $instance;
	
	protected $path;
	protected $method;
	protected $query;
	protected $headers;
	protected $cookies;
	protected $files;
	protected $session;
	protected $server;
	protected $body;
	protected $mimetype;
	protected $queryParams;
	protected $pathParams;
	protected $bodyParams;
	protected $params;
	
	protected static $mimetypes = array(
		'xhtml'		=> 'application/html+xml',
		'html'		=> 'text/html',
        'xml'		=> 'text/xml',
        'csv'		=> 'text/csv',
        'txt'		=> 'text/plain',
        'jsonp'		=> 'text/javascript',
        'json'		=> 'application/json',
        'rss'		=> 'application/rss+xml',
        'atom'		=> 'application/atom+xml',
	);
	
	/**
	 * Returns the global Request object.
	 * 
	 * @return \WordPress\Http\Request
	 */
	public static function instance() {
		if (! isset(static::$instance)) {
			static::$instance = static::createFromGlobals();
		}
		return static::$instance;
	}
	
	/**
	 * Build a request from $_SERVER, $_COOKIE, $_FILES (and possibly $_POST) superglobals.
	 * 
	 * @return \WordPress\Http\Request
	 */
	public static function createFromGlobals() {
		
		$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
		$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
		
		if (isset($_SERVER['PATH_INFO'])) {
			$path = $_SERVER['PATH_INFO'];
		} else {
			$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		}
		
		if (function_exists('apache_request_headers')) {
			$_headers = apache_request_headers();
		} else {
			$_headers = array();
			$others = array(
				'CONTENT_TYPE' => 1, 
				'CONTENT_LENGTH' => 1, 
				'CONTENT_MD5' => 1, 
				'AUTH_TYPE' => 1, 
				'PHP_AUTH_USER' => 1, 
				'PHP_AUTH_PW' => 1, 
				'PHP_AUTH_DIGEST' => 1
			);
			foreach ($_SERVER as $key => $value) {
				if (0 === strpos($key, 'HTTP_') || isset($others[$key])) {
					$_headers[$key] = $value;
				}
			}
		}
		
		$headers = array();
		foreach ($_headers as $key => $value) {
			$name = str_replace(['http_', '_'], ['', '-'], strtolower($key));
			$headers[$name] = $value;
		}
		
		$multipartFormData = false;
		
		if ('POST' === $method && isset($headers['content-type'])) {
			$multipartFormData = stripos($headers['content-type'], 'multipart/form-data') !== false;
		}
		
		if ($multipartFormData) {
			// Use php://input except for POST with "multipart/form-data"
			// @see {@link http://us3.php.net/manual/en/wrappers.php.php}
			$body = $_POST;
		} else if ('HEAD' === $method) {
			$body = array();
		} else {
			$body = file_get_contents('php://input');
		}
		
		return new static($method, $path, $query, $headers, $body, $_SERVER, $_COOKIE, $_FILES);
	}

	/**
	 * Construct the object
	 * 
	 * @param string $method HTTP method.
	 * @param string $uriPath URI path.
	 * @param string $queryString Query string.
	 * @param array $headers Associative array of request headers.
	 * @param mixed $body Request body as string (form-urlencoded or json), array, or object.
	 * @param array $server Server parameters.
	 * @param array $cookies Request cookies.
	 * @param array $files File upload data.
	 */
	public function __construct(
		$method, 
		$uriPath, 
		$queryString, 
		array $headers, 
		$body = null, 
		array $server = array(), 
		array $cookies = array(), 
		array $files = array()
	) {
		
		$this->bodyParams = array();
		$this->queryParams = array();
		
		$this->headers	= $this->parseHeaders($headers);
		$this->server	= $this->parseServer($server);
		$this->cookies	= $this->parseCookies($cookies);
		$this->files	= $this->parseFiles($files);
		$this->body		= $this->parseBody($body);
		$this->path		= $this->parsePath($uriPath);
		$this->query	= $this->parseQuery($queryString);
		$this->method	= $this->parseMethod($method);
		$this->params	= array_merge($this->queryParams, $this->bodyParams);
		
		if (! isset(static::$instance)) {
			static::$instance = $this;
		}
		
		if (method_exists($this, 'onConstruct')) {
			$this->onConstruct();
		}
	}
		
	/**
	 * Returns a property or parameter value.
	 * 
	 * @param string $var Property or parameter name to retrieve.
	 * 
	 * @return mixed Value of property or parameter, if set, otherwise null.
	 */
	public function __get($var) {
		if (isset($this->$var)) {
			return $this->$var;
		}
		if (isset($this->params[$var])) {
			return $this->params[$var];
		}
		return null;
	}
	
	/**
	 * Returns the request HTTP method.
	 * 
	 * @return string HTTP method.
	 */
	public function getMethod() {
		return $this->method;
	}
	
	/**
	 * Returns the raw body content as passed to the constructor.
	 * 
	 * @return mixed
	 */
	public function getBody() {
		return $this->body;
	}
	
	/**
	 * Returns the request URI path.
	 * 
	 * @return string URI
	 */
	public function getPath() {
		return $this->path;	
	}
	
	/**
	 * Returns the request query string if set.
	 * 
	 * @return string Query
	 */
	public function getQuery() {
		return $this->query;	
	}
	
	/**
	 * Returns the full request URI including query string.
	 * 
	 * @return string
	 */
	public function getUri() {
		return $this->path.(empty($this->query) ? '' : '?'.$this->query);
	}
	
	/**
	 * Returns array of parsed headers.
	 * 
	 * @return array HTTP request headers.
	 */
	public function getHeaders() {
		return $this->headers;	
	}
	
	/**
	 * Returns a single HTTP header if set.
	 * 
	 * @param string $name Header name (lowercase).
	 * 
	 * @return string Header value if set, otherwise null.
	 */
	public function getHeader($name) {
		$name = strtolower($name);
		return isset($this->headers[$name]) ? $this->headers[$name] : null;	
	}
	
	/**
	 * Checks whether a given header exists.
	 * 
	 * @param string $name Header name (lowercase).
	 * 
	 * @return boolean True if header exists, otherwise false.
	 */
	public function hasHeader($name) {
		$name = strtolower($name);
		return isset($this->headers[$name]);
	}
	
	/**
	 * Returns the request cookies.
	 * 
	 * @return array Associative array of cookies sent with request.
	 */
	public function getCookieParams() {
		return $this->cookies;
	}
	
	/**
	 * Returns the request files.
	 * 
	 * @return array Files from $_FILES superglobal.
	 */
	public function getFileParams() {
		return $this->files;
	}
	
	/**
	 * Returns the server parameters.
	 * 
	 * @return array
	 */
	public function getServerParams() {
		return $this->server;
	}
	
	/**
	 * Returns a server paramter value by name, if it exists.
	 * 
	 * @param string $name
	 * 
	 * @return mixed
	 */
	public function getServerParam($name) {
		return isset($this->server[$name]) ? $this->server[$name] : null;
	}
	
	/**
	 * Returns all request parameters.
	 * 
	 * @return array Query, path, and body parameters.
	 */
	public function getParams() {
		return $this->params;
	}
	
	/**
	 * Returns a parameter value if set and not null.
	 * 
	 * @param string $name Parameter name.
	 * 
	 * @return mixed Parameter value.
	 */
	public function getParam($name) {
		return isset($this->params[$name]) ? $this->params[$name] : null;
	}
	
	/**
	 * Checks whether a parameter exists.
	 * 
	 * @param string $name Parameter name.
	 * 
	 * @return boolean True if parameter exists, otherwise false.
	 */
	public function hasParam($name) {
		return array_key_exists($name, $this->params);
	}
	
	/**
	 * Checks whether the given parameters exist.
	 * 
	 * @param string|array $keys
	 * @param ...
	 * 
	 * @return boolean
	 */
	public function hasParams($keys) {
		if (! is_array($keys)) {
			$keys = func_get_args();
		}
		foreach($keys as $key) {
			if (! array_key_exists($key, $this->params)) {
				return false;
			}
		}
		return true;
	}
	
	public function getCleanParam($key, $filters = array('trim', 'strip_tags')) {
		$value = $this->getParam($key);
		if (null !== $value) {
			if (is_string($filters)) {
				$filters = explode('|', $filters);
			}
			foreach($filters as $fn) {
				$value = $fn($value);
			}
		}
		return $value;
	}
	
	/**
	 * Returns the name of the currently running script.
	 * 
	 * @return string
	 */
	public function getScriptName() {
		return ltrim($this->getServerParam('SCRIPT_NAME'), '/');
	}
	
	/**
	 * Returns the mimetype requested via file extension or parameter.
	 * 
	 * @return string Mimetype if set, otherwise null.
	 */
	public function getMimetype() {
		return isset($this->mimetype) ? $this->mimetype : null;
	}
	
	/**
	 * Checks whether request is an XML HTTP request.
	 * 
	 * @return boolean True if XMLHttpRequest, otherwise false.
	 */
	public function isXhr() {
		return isset($this->headers['x-requested-with']) 
			&& 'xmlhttprequest' === strtolower($this->headers['x-requested-with']);
	}
	
	/**
	 * Boolean method/xhr checker.
	 * 
	 * @param string $thing HTTP method name, or 'xhr' or 'ajax'.
	 * 
	 * @return boolean True if request is given thing, or false if it is not.
	 */
	public function is($thing) {
		$thing = strtoupper($thing);
		if ($thing === 'XHR' || $thing === 'AJAX') {
			return $this->isXhr();
		}
		return $thing === $this->method;
	}
	
	/**
	 * Checks whether the request sent an 'Accept' header for the given type(s).
	 * 
	 * @param array|string $contentType
	 * @param ...
	 * 
	 * @return boolean
	 */
	public function accepts($contentType) {
		$accepts = $this->getHeader('accept');
		if (! is_array($contentType)) {
			$contentType = func_get_args();
		}
		foreach($contentType as $type) {
			if (isset(static::$mimetypes[$type])) {
				$type = static::$mimetypes[$type];
			}
			if (strpos($accepts, $type) !== false) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Returns the object's properties as an array.
	 * 
	 * @return array
	 */
	public function toArray() {
		return get_object_vars($this);
	}
	
	/**
	 * Sets the parameters matched from the request route.
	 * 
	 * @param array $params Associative array of path parameters.
	 */
	public function setPathParams(array $params) {
		$this->pathParams = $params;
		$this->params = array_merge($this->params, $this->pathParams);
	}
	
	protected function parseHeaders(array $headers) {
		return $headers;
	}

	protected function parseServer(array $server) {
		return $server;
	}
	
	protected function parseCookies(array $cookies) {
		return $cookies;
	}
	
	protected function parseFiles(array $files) {
		return $files;
	}
	
	protected function parseBody($body) {
		
		if (empty($body)) {
			return $body;
		}
		
		if (is_string($body)) {
			if (isset($this->headers['content-type'])) {
				if (false !== stripos($this->headers['content-type'], 'www-form-urlencoded')) {
					parse_str($body, $this->bodyParams);
				} else if (false !== stripos($this->headers['content-type'], 'application/json')) {
					$this->bodyParams = json_decode($body, true, 512, JSON_BIGINT_AS_STRING);
				}
			}
		} else if (is_array($body)) {
			$this->bodyParams = $body;
		} else if (is_object($body)) {
			$this->bodyParams = is_callable(array($body, 'toArray')) ? $body->toArray() : get_object_vars($body);
		}
		
		return $body;
	}
	
	protected function parsePath($uri) {
		
		$uri = trim(filter_var($uri, FILTER_SANITIZE_URL), '/');
		$extensions = implode('|', array_keys(static::$mimetypes));
		
		if (preg_match("/(\.{$extensions})$/i", $uri, $matches)) {
			$ext = $matches[1];
			$this->mimetype = static::$mimetypes[ltrim($ext, '.')];
			// remove extension and "."
			$uri = substr($uri, 0, strlen($uri) - strlen($ext) - 1);
		}
		
		return $uri;
	}
	
	protected function parseQuery($query) {
		
		if (! empty($query)) {
				
			$query = html_entity_decode(urldecode($query));
			
			parse_str($query, $this->queryParams);
			
			if (isset($this->queryParams[static::FORMAT_PARAM])) {
				$type = strtolower($this->queryParams[static::FORMAT_PARAM]);
				if (isset(static::$mimetypes[$type])) {
					$this->mimetype = static::$mimetypes[$type];
				}
			}
		}
		
		return $query;
	}
	
	protected function parseMethod($method) {
		
		$method = strtoupper($method);
		
		if ('POST' === $method) {
			if (isset($this->queryParams[static::METHOD_OVERRIDE_PARAM])) {
				$method = strtoupper($this->queryParams[static::METHOD_OVERRIDE_PARAM]);
			} else if (isset($this->headers['x-http-method-override'])) {
				$method = strtoupper($this->headers['x-http-method-override']); 
			}
		}
		
		return $method;
	}
	
}
