<?php

namespace WordPress\Rewrite;

class Rule
{
	
	const WILD = '.*';
	const ANY = '[^/]+';
	const NUM = '[0-9]+';
	
	protected static $tokenNames = [':wild', ':any', ':num'];
	protected static $tokenRegex = ['.*', '[^/]+', '[0-9]+'];
	
	protected $regex;
	protected $path = 'index.php';
	protected $query = array();
	protected $prepend = false;
	protected $tags = array();
	protected $redirectUri;

	protected static $instances = array();
	
	public static function instance($regex) {
		if (! isset(static::$instances[$regex])) {
			static::$instances[$regex] = new static($regex);
		}
		return static::$instances[$regex];
	}
	
	public function __construct($regex = null) {
		if ($regex) {
			$this->setRegex($regex);
		}
		static::$instances[$this->getRegex()] = $this;
		$this->register();
	}
	
	public function getRegex() {
		return $this->regex;
	}

	public function setRegex($regex) {
		$this->regex = str_replace(static::$tokenNames, static::$tokenRegex, $regex);
		if (substr($this->regex, -1) === '/') {
			$this->regex .= '?$';
		}
		return $this;
	}

	public function getPath() {
		return $this->path;
	}

	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	public function getQuery() {
		return $this->query;
	}

	public function setQuery($args) {
		if (is_string($args)) {
			parse_str($args, $this->query);
		} else {
			$this->query = (array)$args;
		}
		return $this;
	}

	public function getTags() {
		return $this->tags;
	}
	
	public function setTags(array $tags) {
		$this->tags = $tags;
		return $this;
	}

	public function isPrepended() {
		return $this->prepend;
	}
	
	public function prepend($value = true) {
		$this->prepend = (bool)$value;
		return $this;
	}
	
	final public function getRulePosition() {
		return $this->isPrepended() ? 'top' : 'bottom';
	}
	
	public function setRedirectUri($uri) {
		$this->redirectUri = $uri;
	}
	
	public function getRedirectUri() {
		if (! isset($this->redirectUri)) {
			$qs = $this->buildQueryString();
			$this->redirectUri = $this->path.(empty($qs) ? '' : '?'.$qs);
		}
		return $this->redirectUri;
	}

	public function register() {
		if (! did_action('init')) {
			add_action('init', array($this, '_register'), 100);
		} else {
			$this->_register();
		}
		return $this;
	}

	public function _register() {
		foreach($this->getTags() as $tag => $regex) {
			add_rewrite_tag("%{$tag}%", $regex);
		}
		add_rewrite_rule($this->getRegex(), $this->getRedirectUri(), $this->getRulePosition());
	}

	protected function buildQueryString() {
		
		$q = '';
		
		foreach($this->query as $k => $v) {
			if (is_int($v)) {
				$v = '$matches['.$v.']';
			}
			$q .= $k.'='.$v.'&';
		}
		
		return empty($q) ? $q : rtrim($q, '&');
	}
	
}
