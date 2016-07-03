<?php

namespace WordPress\Ajax;

use RuntimeException;

class Controller
{
	
	protected $action;
	protected $callback;
	protected $noPrivCallback;
	
	public function __construct($action = null, callable $callback = null) {
		$this->action = $action;
		$this->callback = $callback;
	}
	
	public function setAction($action) {
		$this->action = $action;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function setCallback(callable $callback) {
		$this->callback = $callback;
	}
	
	public function getCallback() {
		return isset($this->callback) ? $this->callback : array($this, '__invoke');
	}
	
	public function setNoPrivCallback(callable $callback) {
		$this->noPrivCallback = $callback;
	}
	
	public function getNoPrivCallback() {
		return isset($this->noPrivCallback) ? $this->noPrivCallback : $this->getCallback();
	}

	public function isAuthorized() {
		return is_user_logged_in();
	}
	
	public function register() {
		if (empty($this->action)) {
			throw new RuntimeException("Cannot register AJAX controller: missing 'action'.");
		}
		if (empty($this->callback)) {
			throw new RuntimeException("Cannot register AJAX controller: missing 'callback'.");
		}
		$callback = array($this, 'execute');
		add_action('wp_ajax_'.$this->action, $callback);
		add_action('wp_ajax_no_priv_'.$this->action, $callback);
	}
	
	public function execute() {
		if ($this->isAuthorized()) {
			$callback = $this->callback;
		} else {
			$callback = isset($this->noPrivCallback) ? $this->noPrivCallback : $this->callback;
		}
		return $callback($this);
	}
	
}