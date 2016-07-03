<?php

namespace WordPress\Http;

/**
* Represents a response from a WordPress HTTP API request.
*/
class Response extends \ArrayObject
{
	
	public function __construct($response) {
		parent::__construct($response, \ArrayObject::ARRAY_AS_PROPS);
	}
	
	public function getBody() {
		return $this->offsetGet('body');	
	}
	
	public function getCookies() {
		return $this->offsetGet('cookies');
	}
	
	public function getHeaders() {
		return $this->offsetGet('headers');	
	}
	
	public function getHeader($name) {
		return $this->offsetExists('headers') && isset($this->headers[$name]) ? $this->headers[$name] : null;	
	}
	
	public function getContentType() {
		return $this->getHeader('content-type');	
	}
	
	public function getResponse($part = null) {
		
		if ($this->offsetExists('response')) {
			
			if (empty($part)) {
				return $this->response;
			}
			
			if (isset($this->response[$part])) {
				return $this->response[$part];
			}
		}
		
		return null;	
	}
	
	public function isContentType($type) {
		
		if (! $contentType = $this->getContentType()) {
			return null;
		}
		
		return ($type == $contentType || strpos($contentType, $type) !== false);
	}
	
	public function getBodyObject() {
		
		if ($this->offsetExists('body')) {
			if ($this->isContentType('json')) {
				return json_decode($this->body);
			} else if ($this->isContentType('xml')) {
				libxml_use_internal_errors();
				return simplexml_load_string($this->body);
			} else {
				return (object)$this->body;
			}
		}
		
		return null;	
	}
	
	public function __toString() {
		return $this->offsetExists('body') ? (string)$this->body : '';
	}
	
}