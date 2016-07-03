<?php

namespace WordPress\Http\Client;

use ArrayObject;

/**
 * Represents an HTTP response.
 */
class Response extends ArrayObject
{

	public function __construct($response) {
		parent::__construct($response, ArrayObject::ARRAY_AS_PROPS);
		if (isset($this['_start_time'])) {
			$this['request_time'] = microtime(true) - $this['_start_time'];
			unset($this['_start_time']);
		}
	}

	public function getBody() {
		return $this['body'];
	}

	public function getCookies() {
		return $this['cookies'];
	}

	public function getHeaders() {
		return $this['headers'];
	}

	public function getHeader($name) {
		return isset($this['headers']) && isset($this['headers'][$name]) ? $this['headers'][$name] : null;
	}

	public function getContentType() {
		return $this->getHeader('content-type');
	}

	public function getResponse($part = null) {
		
		if (isset($this['response'])) {
		
			if (empty($part)) {
				return $this['response'];
			}
		
			if (isset($this['response'][$part])) {
				return $this['response'][$part];
			}
		}

		return null;
	}

	public function isContentType($type) {
		if (! $contentType = $this->getContentType()) {
			return null;
		}
		return ($type === $contentType || strpos($contentType, $type) !== false);
	}

	public function getBodyDecoded() {

		if (isset($this['body'])) {

			if ($this->isContentType('json')) {
				return json_decode($this['body']);
				
			} else if ($this->isContentType('xml')) {
				libxml_use_internal_errors();
				return simplexml_load_string($this['body']);
			
			} else {
				return (object)$this['body'];
			}
		}

		return null;
	}

	public function __toString() {
		return isset($this['body']) ? (string)$this['body'] : '';
	}

}
