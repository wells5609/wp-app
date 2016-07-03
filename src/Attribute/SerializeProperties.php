<?php

namespace WordPress\Attribute;

trait SerializeProperties
{

	public function serialize() {
		return serialize(get_object_vars($this));
	}
	
	public function unserialize($serial) {
		foreach(unserialize($serial) as $key => $value) {
			$this->$key = $value;
		}
	}
	
}