<?php

class Reserve_Object extends Object {
	
	
	protected function onImport(){
		$helper = get_helper('reserve');
		
		$this->energy_equivalent = $helper->convert_to_energy_equivalent($this->type, $this->quantity);
		
		$this->quantity = number_format($this->quantity, 0);
		
		$this->gt_co2 = number_format($this->gt_co2, 2);
		
	}
	
	function __wakeup(){
		
	}
	function __sleep(){
		return array_keys( get_object_vars($this) );	
	}
		
}