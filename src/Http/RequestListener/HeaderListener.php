<?php

namespace WordPress\Http\RequestListener;

class HeaderListener extends AbstractListener
{
	
	protected $header;
	
	public function __construct($header) {
		$this->header = $header;
		$this->init();
	}
	
	protected function isActive() {
		return false !== getenv('HTTP_'.$this->header);
	}
	
	protected function getValue() {
		return getenv('HTTP_'.$this->header);
	}
	
}
