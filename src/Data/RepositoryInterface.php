<?php

namespace WordPress\Data;

/**
 * Contract for a storage "gateway" for a single entity type.
 */
interface RepositoryInterface 
{
	
	/**
	 * Returns the name of the entity type stored by the repository.
	 * 
	 * @return string
	 */
	public function getEntityTypeName();

	/**
	 * Returns all entities matching $args.
	 * 
	 * @param mixed $args [Optional]
	 * 
	 * @return array
	 */
	public function find($args = null);
	
	/**
	 * Returns a single entity matching $args.
	 * 
	 * @param mixed $args
	 * 
	 * @return \WordPress\Data\EntityInterface
	 */
	public function findOne($args);
	
	/**
	 * Saves the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function save(EntityInterface $entity);
	
	/**
	 * Deletes the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function delete(EntityInterface $entity);
	
}
