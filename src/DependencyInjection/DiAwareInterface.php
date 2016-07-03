<?php

namespace WordPress\DependencyInjection;

interface DiAwareInterface
{

	/**
	 * Set the DI object.
	 *
	 * @param \WordPress\DependencyInjection\Container
	 */
	public function setDI(Container $di);
	
	/**
	 * Returns the DI object.
	 *
	 * @return \WordPress\DependencyInjection\Container
	 */
	public function getDI();
	
}
