<?php

namespace WordPress\Data\User;

use WordPress\Data\RepositoryInterface;
use WordPress\Data\EntityInterface;

class Repository implements RepositoryInterface
{
	
	/**
	 * @var \WordPress\Data\User\Factory
	 */
	protected $factory;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Data\User\Factory $factory
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
		return 'user';
	}
	
	/**
	 * Returns the Term factory.
	 * 
	 * @return \WordPress\Data\User\Factory
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
		$users = get_users((array)$args);
		return empty($users) ? array() : array_map(array($this->factory, 'create'), $users);
	}
	
	/**
	 * Returns a single entity matching $args.
	 * 
	 * @param mixed $args
	 * 
	 * @return \WordPress\Data\EntityInterface
	 */
	public function findOne($args) {
		
		if (is_array($args)) {
			$users = get_users($args);
		} else {	
			if (is_numeric($args)) {
				$wp_user = Lookup::byID($args);
			} else {
				$wp_user = Lookup::byString($args);
			}
			if ($wp_user) {
				$users = array($wp_user);
			}
		}
		
		return empty($users) ? null : call_user_func(array($this->factory, 'create'), reset($users));
	}
	
	/**
	 * Saves the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return int
	 */
	public function save(EntityInterface $entity) {
		
		if (isset($entity->ID)) {
			return wp_update_user($entity->getStorageData());
		}
		
		if ($newUserId = wp_insert_user(array_filter($entity->getStorageData()))) {
			$entity->hydrate(array('ID' => $newUserId));
			return 1;
		}
		
		return 0;
	}
	
	/**
	 * Deletes the given entity.
	 * 
	 * @param \WordPress\Data\EntityInterface $entity
	 * 
	 * @return boolean
	 */
	public function delete(EntityInterface $entity) {
		if (empty($entity->ID)) {
			return false;
		}
		return wp_delete_user($entity->ID);
	}
	
}
