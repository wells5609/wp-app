<?php

namespace WordPress\Data\Core\User;

use WordPress\Data\ModelInterface;
use WordPress\Data\Core\AbstractStorage;

class Storage extends AbstractStorage
{
	
	protected $defaultArguments = array(
	);
	
	/**
	 * Returns the name of the storage container.
	 *
	 * @return string
	 */
	public function getName() {
		return 'users';
	}
	
	/**
	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public function find($args) {
		$records = get_users(wp_parse_args($args, $this->defaultArguments));
		return empty($records) ? array() : $this->factory->createArray($records);
	}
	
	/**
	 * Retrieves a single record from storage.
	 *
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public function findOne($args) {
		$records = get_users(wp_parse_args($args, $this->defaultArguments));
		return empty($records) ? null : $this->factory->create(reset($records));
	}
	
	/**
	 * Saves a record to storage.
	 *
	 * @param \WordPress\Data\ModelInterface $model
	 */
	public function save(ModelInterface $model) {
		
		if ($model->ID) {
			$result = wp_update_user($model->getModelData());
		} else {
			$result = wp_insert_user($model->getModelData());
			if (is_int($result)) {
				$model->ID = $result;
			}
		}
		
		if ($result && is_wp_error($result)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Deletes a record from storage.
	 *
	 * @param \WordPress\Data\ModelInterface $model
	 */
	public function delete(ModelInterface $model) {
		if (! $model->ID) {
			throw new \RuntimeException("User must have 'ID' field to delete.");
		}
		return wp_delete_user($model->ID);
	}	
	
}