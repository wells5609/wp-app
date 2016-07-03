<?php

namespace WordPress\Http\RequestListener;

class ParameterListener extends AbstractListener
{
	
	protected $parameter;
	
	public function __construct($parameter) {
		$this->parameter = $parameter;
		$this->init();
	}
	
	protected function isActive() {
		return isset($_REQUEST[$this->parameter]);
	}
	
	protected function getValue() {
		return $_REQUEST[$this->parameter];
	}
	
}
