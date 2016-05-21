<?php

namespace WordPress\Data\User;

use WP_User;
use WordPress\Data\Entity;

class User extends Entity
{
	public $ID;
	public $user_login;
	public $user_pass;
	public $user_nicename;
	public $user_email;
	public $user_url;
	public $user_registered;
	public $user_activation_key;
	public $user_status;
	public $display_name;
	public $caps;
	public $cap_key;
	public $roles;
	public $allcaps;
	public $filter;
	
	protected $uri;
	
	public function __construct($data = null) {
		if (! isset($data)) {
			$data = wp_get_current_user();
		} else if (! $data instanceof WP_User) {
			if (is_numeric($data)) {
				$data = Lookup::byID($data);
			} else {
				$data = Lookup::byString($data);
			}
		}
		parent::__construct($data);
	}
	
	public function getRepository() {
		return di('users');
	}
	
	public function hydrate($data) {
		if ($data instanceof WP_User) {
			$data = get_object_vars($data);
		//	$data = $data->to_array();
		}
		if (isset($data['data'])) {
			$user_data = (array)$data['data'];
			unset($data['data']);
			$data = array_merge($data, $user_data);
		}
		parent::hydrate($data);
	}
	
	public function getUri() {
		return $this->user_url;
	}
	
	public function __toString() {
		return $this->display_name;
	}
	
	protected function propertyGet($var) {
		if (property_exists($this, $var)) {
			return $this->$var;
		} else if (property_exists($this, 'user_'.$var)) {
			return $this->{'user_'.$var};
		}
		return null;
	}
	
	protected function propertyExists($var) {
		return property_exists($this, $var) || property_exists($this, 'user_'.$var);
	}
	
	protected function propertySet($var, $value) {
		if (property_exists($this, $var)) {
			$this->$var = $value;
		} else if (property_exists($this, 'user_'.$var)) {
			$this->{'user_'.$var} = $value;
		}
	}
	
}
