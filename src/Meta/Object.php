<?php

class Meta_Object extends Object {
	
	public function is_updatable(){
		return ( $this->update_interval !== 0 ) ? true : false;
	}
	
	public function get_next_update(){
		return ($this->time_updated + $this->update_interval);
	}
	
	public function get_last_update(){
		return $this->time_updated;	
	}
	
	public function is_expired(){
		if ( !$this->is_updatable() )
			return false;
		if ( time() > $this->get_next_update() )
			return true;
		return false;
	}
	
}
