<?php

namespace WordPress\Http\Client;

use InvalidArgumentException;
use WP_Error;

/**
 * Represents an HTTP request using WordPress' HTTP API.
 */
class Request
{

	protected $method;
	protected $timeout = 5;
	protected $redirection = 5;
	protected $httpversion = '1.0';
	protected $user_agent;
	protected $blocking = true;
	protected $headers = array();
	protected $cookies = array();
	protected $body;
	protected $compress = false;
	protected $decompress = true;
	protected $sslverify = true;
	protected $stream = false;
	protected $filename;
	
	public function __get($key) {
		return property_exists($this, $key) ? $this->$key : null;
	}
	
	public function __set($key, $value) {
		if (property_exists($this, $key)) {
			$this->$key = $value;
		}
	}
	
	public function setTimeout($timeout) {
		$this->timeout = (int)$timeout;
	}
	
	public function setMaxRedirects($num_redirects) {
		$this->redirection = (int)$num_redirects;
	}
	
	public function setHttpVersion($version) {
		if ('1.0' != $version && '1.1' != $version) {
			throw new InvalidArgumentException("Invalid HTTP version: '{$version}'.");
		}
		$this->httpversion = $version;
	}
	
	public function setUserAgent($user_agent) {
		$this->user_agent = $user_agent;
	}
	
	public function setBlocking($blocking) {
		$this->blocking = $blocking;
	}
	
	public function setCookie($name, $value) {
		$this->cookies[$name] = $value;
	}
	
	public function setBody($content) {
		$this->body = $content;
	}
	
	public function setHeader($name, $value, $replace = true) {
		if ($replace || ! isset($this->headers[$name])) {
			$this->headers[$name] = $value;
		}
	}

	public function addHeader($name, $value) {
		$this->setHeader($name, $value, false);
	}

	public function removeHeader($name, $value = '') {
		if (isset($this->headers[$name])) {
			if (empty($value) || $value == $this->headers[$name]) {
				unset($this->headers[$name]);
			}
		}
	}

	public function basicAuth($username, $password) {
		$this->setHeader('Authorization', 'Basic '.base64_encode($username.':'.$password));
	}

	public function get($url) {
		return $this->request($url, 'GET');
	}

	public function post($url) {
		return $this->request($url, 'POST');
	}

	public function put($url) {
		return $this->request($url, 'PUT');
	}

	public function request($url, $method = 'GET') {
		
		$this->method = strtoupper($method);
		$args = array();

		foreach(get_object_vars($this) as $prop => $val) {
			$args[str_replace('_', '-', $prop)] = $this->$prop;
		}

		$start_time = microtime(true);
		$response = _wp_http_get_object()->request($url, $args);

		if ($response instanceof WP_Error) {
			return $response;
		}
		
		$response = (array)$response;
		
		$response['_start_time'] = $start_time;

		return new Response($response);
	}

}
