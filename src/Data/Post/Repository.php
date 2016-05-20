<?php

namespace WordPress\Data\Post;

use WordPress\Data\RepositoryInterface;
use WordPress\Data\EntityInterface;

class Repository implements RepositoryInterface
{
	
	/**
	 * @var \WordPress\Data\Post\Factory
	 */
	protected $factory;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Data\Post\Factory $factory
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
		return 'post';
	}
	
	/**
	 * Returns the Post factory.
	 * 
	 * @return \WordPress\Data\Post\Factory
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
		$posts = get_posts($args);
		return empty($posts) ? array() : array_map(array($this->factory, 'create'), $posts);
	}
	
	/**
	 * Returns a single entity matching $args.
	 * 
	 * @param mixed $args
	 * 
	 * @return \WordPress\Data\EntityInterface
	 */
	public function findOne($args) {
		$posts = get_posts($args);
		return empty($posts) ? null : call_user_func(array($this->factory, 'create'), reset($posts));
	}
	
	/**
	 * Saves the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function save(EntityInterface $entity) {
		return wp_update_post($entity->getStorageData(), true);
	}
	
	/**
	 * Deletes the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function delete(EntityInterface $entity) {
		return wp_delete_post($entity->ID, true);
	}
	
}
