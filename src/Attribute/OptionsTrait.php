<?php

namespace WordPress\Attribute;

trait WithOptions
{
	
	protected $_options = [];
	
	public function getOptions() {
		return $this->_options;
	}
	
	public function setOption($option, $value = null, $overwrite = true) {
		if ($overwrite || ! isset($this->_options[$option])) {
			$this->_options[$option] = $value;
		}
	}
	
	public function getOption($name) {
		return isset($this->_options[$name]) ? $this->_options[$name] : null;
	}
	
	public function hasOption($name) {
		return isset($this->_options[$name]);
	}
	
	public function addOption($option, $value = null) {
		$this->setOption($option, $value, false);
	}
	
}
