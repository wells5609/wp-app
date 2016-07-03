<?php

namespace WordPress\Cli;

use RuntimeException;

class Context
{
	
	protected $argv;
	protected $env;
	protected $server;
	
	public static function createFromGlobals() {
		if (! isset($GLOBALS['argv'])) {
			throw new RuntimeException('Cannot create from globals: $argv not set.');
		}
		return new static($GLOBALS['argv']);
	}
	
	public function __construct(array $argv, array $env = [], array $server = []) {
		$this->argv = new Context\Argv($argv);
		$this->env = new Context\Env($env);
		$this->server = new Context\Server($server);
	}
	
	public function argv() {
		return $this->argv;
	}
	
	public function env() {
		return $this->env;
	}
	
	public function server() {
		return $this->server;
	}
	
	public function getFilename() {
		return $this->argv->getFilename();
	}
	
	public function getTask() {
		return $this->argv->getTask();
	}
	
	public function getAction() {
		return $this->argv->getAction();
	}
	
	public function getParams() {
		return $this->argv->getParams();
	}
	
	public function getParam($index, $alias = null) {
		return $this->argv->getParam($index, $alias);
	}
	
	public function hasParam($index, $alias = null) {
		return $this->argv->hasParam($index, $alias);
	}
	
	public function getNumParams() {
		return $this->argv->getNumParams();
	}
	
	public function getFlags() {
		return $this->argv->getFlags();
	}
	
	public function hasFlag($name) {
		return $this->argv->hasFlag($name);
	}
	
	public function getNumFlags() {
		return $this->argv->getNumFlags();
	}
	
	public function toArray() {
		return get_object_vars($this);
	}
	
}
