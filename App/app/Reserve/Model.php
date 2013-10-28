<?php

class Reserve_Model extends Model {
	
	
	protected $before_update_field = array(
		'quantity' => array('this.update_co2'),
	);
	
	
}
