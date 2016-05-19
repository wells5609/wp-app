<?php

namespace WordPress\Di;

use WordPress\DI;

interface DiAwareInterface
{
	
	/**
	 * Set the DI object.
	 * 
	 * @param \WordPress\DI
	 */
	public function setDI(DI $di);
	
	/**
	 * Returns the DI object.
	 * 
	 * @return \WordPress\DI
	 */
	public function getDI();
	
}
