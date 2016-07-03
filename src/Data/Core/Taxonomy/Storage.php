<?php

namespace WordPress\Data\Core\Taxonomy;

use WordPress\Data\ModelInterface;
use WordPress\Data\Core\AbstractStorage;

class Storage extends AbstractStorage
{
	
	protected $defaultArguments = array(
		'public' => true,
	);
	
	/**
	 * Returns the name of the storage container.
	 *
	 * @return string
	 */
	public function getName() {
		return 'taxonomies';
	}
	
	/**
	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public function find($args) {
		$records = get_taxonomies(wp_parse_args($args, $this->defaultArguments), 'objects');
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
		if (is_string($args)) {
			$record = get_taxonomy($args);
			return empty($record) ? null : $this->factory->create($record);
		}
		$records = get_taxonomies(array_merge($this->defaultArguments, (array)$args), 'objects');
		return empty($records) ? null : $this->factory->create(reset($records));
	}
	
	/**
	 * Saves a record to storage.
	 *
	 * @param \WordPress\Data\ModelInterface $model
	 */
	public function save(ModelInterface $model) {
		if (! $model->name || ! $model->object_type) {
			throw new \RuntimeException("Taxonomy must have 'name' and 'object_type' fields");
		}
		$result = register_taxonomy($model->name, $model->object_type, $model->getModelData());
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
		if (! $model->name) {
			throw new \RuntimeException("Taxonomy must have 'name' field");
		}
		$result = unregister_taxonomy($model->name);
		return ! is_wp_error($result);
	}	
	
}