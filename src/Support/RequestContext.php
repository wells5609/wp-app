<?php

namespace WordPress\Support;

class RequestContext
{
	const FRONT = 'front';
	const ADMIN = 'admin';
	const REST = 'rest';
	const AJAX = 'ajax';
	const CUSTOM = 'custom';
	
	const ENTRY_INDEX = 'index';
	const ENTRY_ADMIN = 'wp-admin';
	const ENTRY_ADMIN_AJAX = 'admin-ajax';
	const ENTRY_CUSTOM = 'custom';
	
	protected $entrypoint;
	protected $type;
	protected $script;
	
	public function __construct() {
		$this->script = strtolower(ltrim($_SERVER['SCRIPT_NAME'], '/'));
		add_action('parse_request', array($this, 'initialize'), -1);
		add_action('init', array($this, 'initializeFinal'), -1);
	}
	
	public function initialize(\WP $wp) {
		if (isset($wp->query_vars['rest_route'])) {
			$this->type = self::REST;
		} else if ($this->isXhr()) {
			$this->type = self::AJAX;
		}
	}
	
	public function initializeFinal() {
		
		if (is_admin()) {
			$this->type = self::ADMIN;
		} else if ('index.php' === $this->script) {
			$this->type = self::FRONT;
		} else {
			$this->type = self::CUSTOM;
		}
		
		switch ($this->type) {
			
			case self::FRONT:
			case self::REST:
				if ('index.php' === $this->script) {
					$this->entrypoint = self::ENTRY_INDEX;
				}
				break;
				
			case self::ADMIN:
				$this->entrypoint = self::ENTRY_ADMIN;
				break;
				
			case self::AJAX:
				if ($this->script === 'admin-ajax.php') {
					$this->entrypoint = self::ENTRY_ADMIN_AJAX;
				}
				break;
		}

		if (! isset($this->entrypoint)) {
			$this->entrypoint = self::ENTRY_CUSTOM;
		}
	}
	
	public function dump() {
		
		var_dump($this);
	}
	
	public function getScriptName() {
		
	}
	
	public function getEntryPoint() {
		
	}
	
	public function isXhr() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' === strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
	}
	
}
