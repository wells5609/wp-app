<?php

namespace WordPress\Data;

interface EntityInterface extends \ArrayAccess, \Serializable
{
	
	/**
	 * Construct the entity optionally with initial data.
	 * 
	 * @param mixed $data [Optional]
	 */
	public function __construct($data = null);
	
	/**
	 * Returns the entity as an associative array.
	 * 
	 * @return array
	 */
	public function toArray();
	
	/**
	 * Returns an array of data to save to the entity's storage repository.
	 * 
	 * @return array
	 */
	public function getStorageData();
	
	/**
	 * Hydrates the entity with the given data.
	 * 
	 * @param mixed $data
	 */
	public function hydrate($data);
	
	/**
	 * Returns the entity storage repository.
	 * 
	 * @return \WordPress\Data\RepositoryInterface
	 */
	public function getRepository();
	
	//public function insert($data = null);
	//public function update($data = null);
	//public function delete();
}
