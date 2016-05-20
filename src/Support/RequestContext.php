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
	const ENTRY_ADMIN = 'admin';
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
		} else if ($this->isXmlHttpRequest()) {
			$this->type = self::AJAX;
		}
	}
	
	public function initializeFinal() {
		
		if (! isset($this->type)) {
			if (is_admin()) {
				$this->type = self::ADMIN;
			} else if ('index.php' === $this->script) {
				$this->type = self::FRONT;
			} else {
				$this->type = self::CUSTOM;
			}
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
	
	public function getType() {
		return $this->type;
	}
	
	public function getScriptName() {
		return $this->script;
	}
	
	public function getEntryPoint() {
		return $this->entrypoint;
	}
	
	public function isFront() {
		return $this->type === self::FRONT;
	}
	
	public function isREST() {
		return $this->type === self::REST;
	}
	
	public function isApi() {
		return $this->isREST();
	}
	
	public function isAdmin() {
		return $this->type === self::ADMIN;
	}
	
	public function isAjax() {
		return $this->type === self::AJAX;
	}
	
	public function isAdminAjax() {
		return $this->entrypoint === self::ENTRY_ADMIN_AJAX;
	}
	
	public function isCustomEntryPoint() {
		return $this->entrypoint === self::ENTRY_CUSTOM;
	}
	
	public function isXmlHttpRequest() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' === strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
	}
	
}
