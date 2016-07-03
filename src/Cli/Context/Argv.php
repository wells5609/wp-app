<?php

namespace WordPress\Cli\Context;

class Argv extends ReadOnlyValues
{

	const DEFAULT_TASK = 'main';
	const DEFAULT_ACTION = 'main';
	
	public function __construct(array $argv) {
		parent::__construct($this->parseArgv($argv));
	}
	
	public function getFilename() {
		return $this->values['filename'];
	}
	
	public function getTask() {
		return $this->values['task'];
	}
	
	public function getAction() {
		return $this->values['action'];
	}
	
	public function getParams() {
		return $this->values['params'];
	}
	
	public function getParam($index, $alias = null) {
		if (isset($this->values['params'][$index])) {
			return $this->values['params'][$index];
		}
		if (isset($alias) && isset($this->values['params'][$alias])) {
			return $this->values['params'][$alias];
		}
		return null;
	}
	
	public function hasParam($index, $alias = null) {
		return isset($this->values['params'][$index]) || (isset($alias) && isset($this->values['params'][$alias]));
	}
	
	public function getNumParams() {
		return count($this->values['params']);
	}
	
	public function getFlags() {
		return array_keys($this->values['flags']);
	}
	
	public function hasFlag($name) {
		return isset($this->values['flags'][$name]);
	}
	
	public function getNumFlags() {
		return count($this->values['flags']);
	}
	
	protected function parseArgv(array $args) {
		
		$filename = array_shift($args);
		$task = empty($args) ? self::DEFAULT_TASK : array_shift($args);
		$action = null;
		$params = array();
		$flags = array();
		
		$done = false;
	
		foreach($args as $arg) {
	
			if ($arg === '--') {
				$done = true;
				continue;
			}
				
			if ($arg[0] === "-" || $done) {
				if (strpos($arg, '=') !== false) {
					list($key, $value) = explode('=', $arg, 2);
					#$this->setParam(ltrim($key, '-'), $value);
					$params[trim(ltrim($key, '-'))] = trim($value);
				} else {
					$arg = ltrim($arg, '-');
					if (strlen($arg) === 1) {
						#$this->flags[$arg] = true;
						$flags[$arg] = true;
					} else {
						#$this->setParam(substr($arg, 0, 1), substr($arg, 1));
						$params[trim(substr($arg, 0, 1))] = trim(substr($arg, 1));
					}
				}
			} else if (strpos($arg, '=') !== false) {
				list($key, $value) = explode('=', $arg, 2);
				$params[trim($key)] = trim($value);
				#$this->setParam($key, $value);
			} else if ($action === null) {
				#$this->action = $arg;
				$action = $arg;
			} else {
				#$this->params[] = $arg;
				$params[] = $arg;
			}
		}
	
		if (null === $action) {
			$action = self::DEFAULT_ACTION;
		}
		
		return compact('filename', 'task', 'action', 'params', 'flags');
	}
	
}