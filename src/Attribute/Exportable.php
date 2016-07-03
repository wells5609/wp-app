<?php

namespace WordPress\Attribute;

trait Exportable
{
	
	public function __export() {
		return var_export($this, true);
	}
	
	public static function __set_state($array) {
		
		$self = new static();
		
		if (method_exists($self, 'hydrate')) {
			$self->hydrate($array);
		} else {
			foreach($array as $key => $value) {
				$self->$key = $value;
			}
		}
		
		return $self;
	}
	
}