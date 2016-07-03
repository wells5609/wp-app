<?php

namespace WordPress\Rest;

use WP_REST_Request;
use WP_REST_Response;

class Manager
{
	
	const URL_PREFIX_OPTION = 'rest_api_url_prefix';
	
	protected $enable = true;
	protected $enableJsonp = true;
	protected $urlPrefix;
	protected $serverClass = 'WordPress\Rest\Server';
	protected $requestClass = 'WP_REST_Request';
	protected $responseClass = 'WP_REST_Response';
	
	protected static $instance;
	
	/**
	 * Returns the singleton instance.
	 * 
	 * @return \WordPress\REST\Manager
	 */
	public static function instance() {
		if (! isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	/**
	 * Constructor.
	 * 
	 * Adds the necessary WP filters/actions.
	 */
	public function __construct() {
		if (isset(static::$instance)) {
			throw new \RuntimeException("Cannot create multiple instances of ".__CLASS__);
		}
		static::$instance = $this;
		add_filter('rest_enabled', function() {
			return $this->enable;
		});
		add_filter('rest_jsonp_enabled', function() {
			return $this->enableJsonp;
		});
		add_filter('rest_url_prefix', function ($prefix) {
			if (! isset($this->urlPrefix)) {
				$this->urlPrefix = get_option(self::URL_PREFIX_OPTION) ?: null;
			}
			return $this->urlPrefix ?: $prefix;
		});
		add_filter('wp_rest_server_class', function ($class) {
			return $this->serverClass ?: $class;
		});
	}
	
	/**
	 * Adds an action to be executed on 'rest_api_init'.
	 * 
	 * Endpoint objects should be added via this method so that they're only created when necessary.
	 * 
	 * @param callable $callback
	 * @param number $priority [Optional] Default = 10
	 * @param number $number_arguments [Optional] Default = 1
	 */
	public function ready(callable $callback, $priority = 10, $number_arguments = 1) {
		add_action('rest_api_init', $callback, $priority, $number_arguments);
		return $this;
	}
	
	/**
	 * Returns the REST route for a given path.
	 * 
	 * @see rest_url()
	 * 
	 * @param string $path
	 * 
	 * @return string
	 */
	public function getUrl($path) {
		return rest_url($path);
	}
	
	/**
	 * Returns the REST server instance (usually WP_REST_Server).
	 * 
	 * @see rest_get_server()
	 * 
	 * @return \WP_REST_Server
	 */
	public function getServer() {
		return rest_get_server();
	}

	/**
	 * Registers a REST API route.
	 * 
	 * @see register_rest_route()
	 *
	 * @param string $namespace The first URL segment after core prefix. Should be unique to your package/plugin.
	 * @param string $route     The base URL for route you are adding.
	 * @param array  $args      Optional. Either an array of options for the endpoint, or an array of arrays for
	 *                          multiple methods. Default empty array.
	 * @param bool   $override  Optional. If the route already exists, should we override it? True overrides,
	 *                          false merges (with newer overriding if duplicate keys exist). Default false.
	 * @return bool True on success, false on error.
	 */
	public function registerRoute($namespace, $route, $args = array(), $override = false) {
		return register_rest_route($namespace, $route, $args, $override);
	}
	
	/**
	 * Disables the REST API.
	 */
	public function disable() {
		$this->enable = false;
	}
	
	/**
	 * Enables the REST API (default).
	 */
	public function enable() {
		$this->enable = true;
	}
	
	/**
	 * Checks whether the REST API is enabled.
	 * 
	 * @return boolean
	 */
	public function isEnabled() {
		return apply_filters('rest_enabled', true);
	}
	
	/**
	 * Disables JSONP support.
	 */
	public function disableJsonp() {
		$this->enableJsonp = false;
	}
	
	/**
	 * Enables JSONP support (default).
	 */
	public function enableJsonp() {
		$this->enableJsonp = true;
	}
	
	/**
	 * Checks whether JSONP is supported.
	 * 
	 * @return boolean
	 */
	public function isJsonpEnabled() {
		return apply_filters('rest_jsonp_enabled', true);
	}
	
	/**
	 * Sets the REST API URL prefix.
	 * 
	 * @param string $prefix
	 */
	public function setUrlPrefix($prefix) {
		$this->urlPrefix = trim($prefix, '/');
		update_option(self::URL_PREFIX_OPTION, $this->urlPrefix);
	}
	
	/**
	 * Returns the REST API URL prefix.
	 */
	public function getUrlPrefix() {
		return rest_get_url_prefix();
	}
	
	/**
	 * Sets the REST API server class name.
	 * 
	 * @param string $classname
	 */
	public function setServerClass($classname) {
		$this->serverClass = $classname;
	}
	
	/**
	 * Returns the REST API server class name.
	 * 
	 * @return string
	 */
	public function getServerClass() {
		return apply_filters('wp_rest_server_class', $this->serverClass);
	}
	
	/**
	 * Sets the REST API request class name.
	 * 
	 * @param string $classname
	 */
	public function setRequestClass($classname) {
		$this->requestClass = $classname;
	}
	
	/**
	 * Returns the REST API request class name.
	 * 
	 * @return string
	 */
	public function getRequestClass() {
		return $this->requestClass;
	}

	/**
	 * Sets the REST API response class name.
	 *
	 * @param string $classname
	 */
	public function setResponseClass($classname) {
		$this->responseClass = $classname;
	}
	
	/**
	 * Returns the REST API response class name.
	 *
	 * @return string
	 */
	public function getResponseClass() {
		return $this->responseClass;
	}
	
	/**
	 * Creates a new REST API request object.
	 * 
	 * @param string $method [Optional] Default = "GET"
	 * @param string $route [Optional]
	 * @param array $attributes [Optional]
	 * 
	 * @return \WP_REST_Request
	 */
	public function createRequest($method = 'GET', $route = '', $attributes = array()) {
		$class = $this->requestClass;
		return new $class($method, $route, $attributes);
	}
	
	/**
	 * Creates a new REST API response object.
	 * 
	 * @param mixed $response
	 * 
	 * @return \WP_REST_Response
	 */
	public function createResponse($response) {
		$class = $this->responseClass;
		return new $class($response);
	}
	
	/**
	 * Ensures the given argument is a WP_REST_Request instance.
	 * 
	 * @see rest_ensure_request()
	 * 
	 * @param mixed $request
	 * 
	 * @return \WP_REST_Request
	 */
	public function ensureRequest($request) {
		if ($request instanceof WP_REST_Request) {
			return $request;
		}
		return $this->createRequest('GET', '', $request);
	}

	/**
	 * Ensures the given argument is a WP_REST_Response instance.
	 *
	 * @see rest_ensure_response()
	 *
	 * @param mixed $response
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function ensureResponse($response) {
		if ($response instanceof WP_HTTP_Response || is_wp_error($response)) {
			return $response;
		}
		return $this->createResponse($response);
	}
	
}