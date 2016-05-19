<?php

namespace WordPress\Di;

use WordPress\DI;

abstract class Injectable implements DiAwareInterface
{
	
	/**
	 * @var \WordPress\DI
	 */
	protected $di;
	
	/**
	 * Set the DI object.
	 * 
	 * @param \WordPress\DI
	 */
	public function setDI(DI $di) {
		$this->di = $di;
	}
	
	/**
	 * Returns the DI object.
	 * 
	 * @return \WordPress\DI
	 */
	public function getDI() {
		return $this->di;
	}
	
}
