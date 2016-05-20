<?php

namespace WordPress\Data\Taxonomy;

use WordPress\Data\RepositoryInterface;
use WordPress\Data\EntityInterface;

class Repository implements RepositoryInterface
{
	
	/**
	 * @var \WordPress\Data\Taxonomy\Factory
	 */
	protected $factory;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Data\Taxonomy\Factory $factory
	 */
	public function __construct(Factory $factory) {
		$this->factory = $factory;
	}
	
	/**
	 * Returns the repository's entity type name.
	 * 
	 * @return string
	 */
	public function getEntityTypeName() {
		return 'taxonomy';
	}
	
	/**
	 * Returns the Taxonomy factory.
	 * 
	 * @return \WordPress\Data\Taxonomy\Factory
	 */
	public function getFactory() {
		return $this->factory;
	}
	
	/**
	 * Returns all entities matching $args.
	 * 
	 * @param mixed $args [Optional]
	 * 
	 * @return array
	 */
	public function find($args = null) {
		$taxonomies = get_taxonomies($args, 'objects');
		return empty($taxonomies) ? array() : array_map(array($this->factory, 'create'), $taxonomies);
	}
	
	/**
	 * Returns a single entity matching $args.
	 * 
	 * @param mixed $args
	 * 
	 * @return \WordPress\Data\EntityInterface
	 */
	public function findOne($args) {
		$taxonomies = get_taxonomies($args, 'objects');
		return empty($taxonomies) ? null : call_user_func(array($this->factory, 'create'), reset($taxonomies));
	}
	
	/**
	 * Saves the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function save(EntityInterface $entity) {
		return register_taxonomy($entity->name, null, $entity->getStorageData());
	}
	
	/**
	 * Deletes the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function delete(EntityInterface $entity) {
		return 0;
	}
	
}
