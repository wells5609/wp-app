<?php

namespace WordPress\Data\Term;

use WordPress\Data\RepositoryInterface;
use WordPress\Data\EntityInterface;
use WordPress\Data\Taxonomy\Taxonomy;

class Repository implements RepositoryInterface
{
	
	/**
	 * @var \WordPress\Data\Term\Factory
	 */
	protected $factory;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Data\Term\Factory $factory
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
		return 'term';
	}
	
	/**
	 * Returns the Term factory.
	 * 
	 * @return \WordPress\Data\Term\Factory
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
		$terms = get_terms($this->filterFindArgs($args));
		return empty($terms) ? array() : array_map(array($this->factory, 'create'), $terms);
	}
	
	/**
	 * Returns a single entity matching $args.
	 * 
	 * @param mixed $args
	 * 
	 * @return \WordPress\Data\EntityInterface
	 */
	public function findOne($args) {
		$terms = get_terms($this->filterFindArgs($args));
		return empty($terms) ? null : call_user_func(array($this->factory, 'create'), reset($terms));
	}
	
	/**
	 * Saves the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function save(EntityInterface $entity) {
		if (isset($entity->term_id)) {
			return wp_update_term($entity->term_id, $entity->taxonomy, $entity->getStorageData());
		}
		return wp_insert_term($entity->name, $entity->taxonomy, array_filter($entity->getStorageData()));
	}
	
	/**
	 * Deletes the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return boolean
	 */
	public function delete(EntityInterface $entity) {
		if (! isset($entity->term_id) || ! isset($entity->taxonomy)) {
			throw new RuntimeException("Cannot delete term: must set 'term_id' and 'taxonomy' properties.");
		}
		return wp_delete_term($entity->term_id, $entity->taxonomy);
	}
	
	protected function filterFindArgs($args = null) {
		if (empty($args)) {
			return array('taxonomy' => Taxonomy::DEFAULT_TAXONOMY);
		} else if (is_string($args)) {
			return array('taxonomy' => $args);
		}
		return $args;
	}
	
}
