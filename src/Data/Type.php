<?php

namespace WordPress\Data;

abstract class Type implements TypeInterface
{
	
	/**
	 * @var \WordPress\Data\StorageInterface
	 */
	protected $storage;
	
	/**
	 * Returns a new model storage container.
	 * 
	 * @return \WordPress\Data\StorageInterface
	 */
	abstract protected function createStorage();
	
	/**
	 * Returns a description of the data type.
	 * 
	 * @return string
	 */
	public function getDescription() {
		return '';
	}
	
	/**
	 * Returns the name of the PHP class for models of this type.
	 * 
	 * @return string
	 */
	public function getModelClassname() {
		return 'WordPress\\Data\\Model';
	}
	
	/**
	 * Returns the associated storage container.
	 * 
	 * @return \WordPress\Data\StorageInterface
	 */
	public function getStorage() {
		if (! isset($this->storage)) {
			$this->storage = $this->createStorage();
		}
		return $this->storage;
	}

	/**
	 * Returns a new model factory.
	 * 
	 * @return \WordPress\Data\FactoryInterface
	 */
	protected function createFactory() {
		return new Factory($this);
	}
	
}
