<?php

namespace WordPress\Option;

class Option
{
	
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var mixed
	 */
	protected $value;
	
	/**
	 * @var mixed
	 */
	protected $default;
	
	/**
	 * @var boolean
	 */
	protected $autoload = true;
	
	public static function get($name, $default = null) {
		return new static($name, \get_option($name, $default), $default);
	}
	
	/**
	 * Constructor
	 */
	public function __construct($name, $value = null, $default = null, $autoload = true) {
		$this->name = $name;
		$this->value = $value;
		$this->default = $default;
		$this->autoload = $autoload;
	}
	
	public function exists() {
		return \get_option($this->name, '@DOESNOTEXIST@') !== '@DOESNOTEXIST@';
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function getDefault() {
		return $this->default;
	}
	
	public function isAutoloaded() {
		return $this->autoload;
	}
	
	public function __toString() {
		return $this->name;
	}
	
	public function __invoke() {
		return isset($this->value) ? $this->value : $this->default;
	}
	
	public function save($value = null) {
		if (null !== $value) {
			$this->value = $value;
		}
		return \update_option($this->name, $this->value, $this->autoload);
	}
	
	public function delete() {
		return \delete_option($this->name);
	}
	
}
