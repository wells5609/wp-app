<?php

namespace WordPress\Model\User;

use WP_User;
use WordPress\Model\Model;

class User extends Model
{
	
	/**
	 * The current user instance.
	 * 
	 * @var \WordPress\Model\User
	 */
	protected static $current;
	
	/**
	 * Returns the current user instance.
	 * 
	 * @return \WordPress\Model\User\User
	 */
	public static function instance() {
		if (! isset(static::$current)) {
			if (! is_user_logged_in()) {
				return null;
			}
			static::$current = static::forgeObject(wp_get_current_user());
		}
		return static::$current;
	}
	
	/**
	 * Overwrite forgeObject() implementation.
	 * 
	 * @param mixed $data
	 * @return \WordPress\Model\User\User
	 */
	public static function forgeObject($data) {
		if (! $data instanceof WP_User) {
			$data = get_userdata($data);
			if (! $data instanceof WP_User) {
				return null;
			}
		}
		return new static($data);
	}
	
	/**
	 * Overwrite import() implementation.
	 * 
	 * @param mixed $data
	 * @return void
	 */
	public function import($data) {
		if ($data instanceof WP_User) {
			$data = $data->to_array();
		} else if (! is_array($data)) {
			$data = is_object($data) ? get_object_vars($data) : (array)$data;
		}
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * Returns the user ID.
	 * 
	 * @return int
	 */
	public function getPrimaryKeyValue() {
		return $this->ID;
	}
	
	/**
	 * Returns user meta.
	 * 
	 * @param string $key [Optional]
	 * @param boolean $single [Optional] Default = false
	 * @return mixed
	 */
	public function getMeta($key = null, $single = false) {
		if (! isset($this->meta)) {
			$this->meta = get_user_meta($this->ID);
		}
		if (! isset($key)) {
			return $this->meta;
		} else if (! isset($this->meta[$key])) {
			return null;
		}
		if ($single && is_array($this->meta[$key])) {
			return reset($this->meta[$key]);
		}
		return $this->meta[$key];
	}
	
	/**
	 * Returns user data or user meta.
	 * 
	 * @param string $var
	 * @return mixed
	 */
	public function __get($var) {
		if (isset($this->$var)) {
			return $this->$var;
		}
		return $this->getMeta($var, true);
	}
	
}
