<?php

namespace WordPress\Data;

use RuntimeException;

class CustomEntity extends Entity
{
	
	/**
	 * The entity's storage repository.
	 * 
	 * @var \WordPress\Data\RepositoryInterface
	 */
	protected $_repository;
	
	/**
	 * Sets the entity storage repository.
	 * 
	 * @param \WordPress\Data\RepositoryInterface $storage
	 */
	public function setRepository(RepositoryInterface $storage) {
		$this->_repository = $storage;
	}
	
	/**
	 * Returns the entity storage repository.
	 * 
	 * @return \WordPress\Data\RepositoryInterface
	 */
	public function getRepository() {
		return $this->_repository;
	}
	
	/**
	 * Inserts the current object data into the database.
	 * 
	 * @param mixed $data [Optional]
	 * 
	 * @return boolean
	 */
	public function insert($data = null) {	
		if (! isset($this->_repository)) {
			throw new RuntimeException("Cannot insert: entity is missing storage repo");
		}
		if (isset($data)) {
			$this->hydrate($data);
		}
		return $this->_repository->save($this);
	}
	
}
