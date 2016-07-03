<?php

namespace WordPress\Utility;

class Uri implements \ArrayAccess
{
	
	protected $scheme;
	protected $host;
	protected $domain;
	protected $subdomains = [];
	protected $port;
	protected $user;
	protected $pass;
	protected $path;
	protected $query;
	protected $fragment;
	
	public function __construct($uri) {
		
		if (! is_array($uri)) {
			if ($uri instanceof Uri) {
				$uri = $uri->toArray();
			} else {
				$uri = static::parse($uri);
			}
		}
		
		$this->import($uri);
	}
	
	public function offsetGet($key) {
		return isset($this->$key) ? $this->$key : null;
	}
	
	public function offsetSet($key, $value) {
		$this->$key = $value;
	}
	
	public function offsetExists($key) {
		return isset($this->$key);
	}
	
	public function offsetUnset($key) {
		if (in_array($key, ['domain', 'subdomains'], true)) {
			throw new \RuntimeException("Cannot unset 'domain' or 'subdomains'.");
		}
		unset($this->$key);
	}
	
	public function toArray() {
		return get_object_vars($this);
	}
	
	public function without($part) {
		$args = $this->toArray();
		foreach(func_get_args() as $part) {
			unset($args[$part]);
		}
		return new static($args);
	}
	
	public function import(array $vars) {
		foreach($vars as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function isResolved() {
		return ! empty($this->host);
	}
	
	public function __toString() {
		
		$url = '';
		
		if (isset($this['scheme'])) {
			$url .= $this['scheme'].'://';
		}
		
		if (isset($this['user'])) {
			$url .= $this['user'];
			if (isset($this['pass'])) {
				$url .= ':'.$this['pass'];
			}
			$url .= '@';
		}
		
		$url .= $this['host'];
		
		if (isset($this['port'])) {
			$url .= ':'.$this['port'];
		}
		
		if (isset($this['path'])) {
			$url .= '/'.trim($this['path'], '/');
		}
		
		if (isset($this['query'])) {
			$url .= '?'.$this['query'];
		}
		
		if (isset($this['fragment'])) {
			$url .= '#'.$this['fragment'];
		}
		
		return $url;
	}
	
	public static function parse($uri) {
		
		$parts = parse_url($uri);
		
		if (isset($parts['host'])) {
			if (false === strpos($parts['host'], '.')) {
				$parts['domain'] = $parts['host'];
			} else {
				$host_parts = explode('.', $parts['host']);
				switch (count($host_parts)) {
					case 1:
						$parts['domain'] = reset($host_parts);
						break;
					case 2:
					default:
						$tld = array_pop($host_parts);
						$parts['domain'] = array_pop($host_parts).".$tld";
						$parts['subdomains'] = $host_parts;
						break;
				}
			}
		}
		
		return $parts;
	}

}
