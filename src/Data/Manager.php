<?php

namespace WordPress\Data;

class Manager
{
	
	/**
	 * Registered data types.
	 * 
	 * @var \WordPress\Data\Type[]
	 */
	protected $types = array();
	
	/**
	 * Storage containers.
	 * 
	 * @var \WordPress\Data\StorageInterface[]
	 */
	protected $storage = array();
	
	/**
	 * Registers a data type.
	 * 
	 * @param \WordPress\Data\Type $type
	 * 
	 * @return \WordPress\Data\Manager
	 */
	public function register(Type $type) {
		$this->types[$type->getName()] = $type;
		return $this;
	}
	
	/**
	 * Checks whether a given data type has been registered.
	 * 
	 * @param string|\WordPress\Data\Type $type
	 * 
	 * @return boolean
	 */
	public function isRegistered($type) {
		if ($type instanceof Type) {
			return in_array($type, $this->types, true);
		}
		return isset($this->types[$type]);
	}
	
	/**
	 * Returns a registered Type by name.
	 * 
	 * @param string $name
	 * 
	 * @return \WordPress\Data\Type|null
	 */
	public function getType($name) {
		return isset($this->types[$name]) ? $this->types[$name] : null;
	}
	
	/**
	 * Attempts to locate the Type associated with a given model.
	 * 
	 * @param \WordPress\Data\ModelInterface $model
	 * 
	 * @return \WordPress\Data\Type|null
	 */
	public function getModelType(ModelInterface $model) {
		$class = get_class($model);
		foreach($this->types as $name => $type) {
			if ($type->getModelClassname() === $class) {
				return $type;
			}
		}
	}
	
	/**
	 * Registers a storage container.
	 * 
	 * @param \WordPress\Data\StorageInterface $storage
	 * 
	 * @return \WordPress\Data\Manager
	 */
	public function addStorage(StorageInterface $storage) {
		$this->storage[$storage->getName()] = $storage;
		return $this;
	}
	
	/**
	 * Returns a storage container for the given type.
	 * 
	 * @param string|\WordPress\Data\Type $type
	 * 
	 * @return \WordPress\Data\StorageInterface|null
	 */
	public function getStorage($type) {
		if ($type instanceof Type) {
			$type = $type->getName();
		}
		$storage = null;
		if (isset($this->storage[$type])) {
			$storage = $this->storage[$type];
		} else if ($type = $this->getType($type)) {
			$storage = $type->createStorage();
			$this->addStorage($storage);
		}
		return $storage;
	}
	
}