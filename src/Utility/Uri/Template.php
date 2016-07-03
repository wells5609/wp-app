<?php

namespace WordPress\Utility\Uri;

class Template 
{
		
	protected $template;
	protected $vars = [];
	protected $optional = [];
	protected $defaults = [];
	protected $strings = [];
	
	public function __construct($template) {
		
		$this->template = trim($template, '/');
		
		if (! preg_match_all('`\{([\w]+(\?([\w]+)?)?)\}`', $this->template, $matches)) {
			throw new \InvalidArgumentException("Invalid URI template.");
		}
		
		foreach($matches[1] as $i => $var) {
			
			$submatch = $matches[2][$i];
			$optional = false;
			$default = '';
			
			// strip '?' and '?<default>' from var name
			if (! empty($submatch)) {
				$var = substr($var, 0, strlen($var) - strlen($submatch));
				$optional = true;
				$default = ltrim($submatch, '?');
			}
			
			$this->vars[$var] = $var;
			$this->strings[$var] = $matches[0][$i];
			
			if ($optional) {
				$this->optional[$var] = $var;
			}
			
			if (! empty($default)) {
				$this->defaults[$var] = $default; 
			}
		}
	}
	
	public function getTemplate() {
		return $this->template;
	}
	
	public function isVar($var) {
		return isset($this->vars[$var]);
	}
	
	public function isOptional($var) {
		return isset($this->optional[$var]);
	}
	
	public function isRequired($var) {
		return $this->isVar($var) && ! $this->isOptional($var);
	}
	
	public function hasDefault($var) {
		return isset($this->defaults[$var]);
	}
	
	public function getDefault($var) {
		return isset($this->defaults[$var]) ? $this->defaults[$var] : null;
	}
		
	public function __invoke($args) {
		
		$args = (array)$args;
		$search = $replace = array();
		
		foreach($this->vars as $var) {
			
			if (isset($args[$var])) {
				$replaceStr = $args[$var];
			} else if (isset($this->defaults[$var])) {
				$replaceStr = $this->defaults[$var];
			} else if (isset($this->optional[$var])) {
				$replaceStr = '';
			} else {
				return false;
			}
			
			$search[] = $this->strings[$var];
			$replace[] = $replaceStr;
		}
		
		return trim(str_replace($search, $replace, $this->template), '/');
	}
	
	public function build(array $args) {
		return $this($args);
	}
	
}
