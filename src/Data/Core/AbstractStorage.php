<?php

namespace WordPress\Data\Core;

use WordPress\Data\FactoryInterface;
use WordPress\Data\StorageInterface;

abstract class AbstractStorage implements StorageInterface
{
	
	protected $factory;
	
	public function __construct(FactoryInterface $factory) {
		$this->factory = $factory;
	}
	
	/**
	 * Returns the model factory.
	 *
	 * @return \WordPress\Data\FactoryInterface
	 */
	public function getFactory() {
		return $this->factory;
	}
	
}