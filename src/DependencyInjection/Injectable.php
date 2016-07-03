<?php

namespace WordPress\DependencyInjection;

abstract class Injectable implements DiAwareInterface
{
	
	/**
	 * @var \WordPress\DependencyInjection\Container
	 */
	protected $di;
	
	/**
	 * Set the DI object.
	 * 
	 * @param \WordPress\DependencyInjection\Container
	 */
	public function setDI(Container $di) {
		$this->di = $di;
	}
	
	/**
	 * Returns the DI object.
	 * 
	 * @return \WordPress\DependencyInjection\Container
	 */
	public function getDI() {
		return $this->di;
	}
	
}
