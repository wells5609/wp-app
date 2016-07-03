<?php

namespace WordPress\Data\Core\Term;

use WordPress\Data\ModelInterface;
use WordPress\Data\Core\AbstractStorage;

class Storage extends AbstractStorage
{
	
	protected $defaultArguments = array(
		'hide_empty' => false,
	);
	
	/**
	 * Returns the name of the storage container.
	 *
	 * @return string
	 */
	public function getName() {
		return 'terms';
	}
	
	/**
	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public function find($args) {
		$records = get_terms(wp_parse_args($args, $this->defaultArguments));
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
		$defaults = array_merge($this->defaultArguments, array('number' => 1));
		$records = get_terms(wp_parse_args($args, $defaults));
		return empty($records) ? null : $this->factory->create(reset($records));
	}
	
	/**
	 * Saves a record to storage.
	 *
	 * @param \WordPress\Data\ModelInterface $model
	 */
	public function save(ModelInterface $model) {
		
		if (! $model->taxonomy) {
			throw new \RuntimeException("Term must have 'taxonomy' field.");
		}
		
		if ($model->term_id) {
			$result = wp_update_term($model->term_id, $model->taxonomy, $model->getModelData());
		} else {
			if (! $model->name) {
				throw new \RuntimeException("New terms must have 'name' field to insert.");
			}
			$result = wp_insert_term($model->name, $model->taxonomy, $model->getModelData());
			if (is_array($result)) {
				$model->hydrate($result);
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
		if (! $model->term_id || ! $model->taxonomy) {
			throw new \RuntimeException("Term must have 'term_id' and 'taxonomy' fields.");
		}
		$result = wp_delete_term($model->term_id, $model->taxonomy);
		return ! is_wp_error($result);
	}	
	
}