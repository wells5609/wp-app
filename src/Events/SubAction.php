<?php

namespace WordPress\Events;

use RuntimeException;

class SubAction
{
	
	protected $name;
	protected $arguments;
	protected $action;
	protected $priority = 10;
	
	private $bound = false;
	private $executing = false;
	private $complete = false;
	
	public function __construct($name, array $arguments = array(), $action = null, $priority = 10) {
		$this->name = $name;
		$this->arguments = $arguments;
		if ($action) {
			$this->bind($action, $priority);
		}
	}
	
	public function bind($action, $priority = 10) {
		if ($this->bound) {
			throw new RuntimeException("Action is already bound to '$this->action'");
		}
		$this->action = $action;
		$this->priority = $priority;
		add_action($this->action, array($this, 'invoke'), $priority);
		$this->bound = true;
	}
	
	public function getBoundAction() {
		return $this->action;
	}
	
	public function getPriority() {
		return $this->priority;
	}
	
	public function getName($name) {
		return $this->name;
	}
	
	public function getArguments() {
		return $this->arguments;
	}
	
	public function isBound() {
		return $this->bound;
	}
	
	public function isExecuting() {
		return $this->executing;
	}
	
	public function isComplete() {
		return $this->complete;
	}
	
	public function invoke() {
		$this->executing = true;
		do_action_ref_array($this->name, $this->arguments);
		$this->executing = false;
		$this->complete = true;
	}
	
}
