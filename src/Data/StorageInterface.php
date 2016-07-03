<?php

namespace WordPress\Data;

interface StorageInterface
{
	
	/**
	 * Returns the name of the storage container.
	 * 
	 * @return string
	 */
	public function getName();
	
	/**
	 * Returns the model factory.
	 *
	 * @return \WordPress\Data\FactoryInterface
	 */
	public function getFactory();
	
	/**
 	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 * 
	 * @return mixed
	 */
	public function find($args);
	
	/**
 	 * Retrieves a single record from storage.
	 *
	 * @param mixed $args
	 * 
	 * @return mixed
	 */
	public function findOne($args);
	
	/**
	 * Saves a record to storage.
	 * 
	 * @param \WordPress\Data\ModelInterface $model
	 */
	public function save(ModelInterface $model);
	
	/**
	 * Deletes a record from storage.
	 * 
	 * @param \WordPress\Data\ModelInterface $model
	 */
	public function delete(ModelInterface $model);
	
}
