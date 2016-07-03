<?php

namespace WordPress\Data\Core\Post;

use WordPress\Data\Core\AbstractStorage;
use WordPress\Data\ModelInterface;

class Storage extends AbstractStorage
{
	
	protected $defaultPostArgs = array(
		'post_type' => 'post',
	);
	
	/**
	 * Returns the name of the storage container.
	 *
	 * @return string
	 */
	public function getName() {
		return 'posts';
	}
	
	/**
	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public function find($args) {
		$args = wp_parse_args($args, $this->defaultPostArgs);
		$records = get_posts($args);
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
		$args = wp_parse_args($args, $this->defaultPostArgs);
		$args['numberposts'] = 1;
		$records = get_posts($args);
		return empty($records) ? null : $this->factory->create(reset($records));
	}
	
	/**
	 * Saves a record to storage.
	 *
	 * @param \WordPress\Data\ModelInterface $model
	 */
	public function save(ModelInterface $model) {
		$data = $model->getModelData();
		if ($model->ID) {
			$result = wp_update_post($data);
		} else {
			$result = wp_insert_post($data);
		}
		return $result;
	}
	
	/**
	 * Deletes a record from storage.
	 *
	 * @param \WordPress\Data\ModelInterface $model
	 */
	public function delete(ModelInterface $model) {
		if ($model->ID) {
			return (bool)wp_delete_post($model->ID);
		}
		return true;
	}	
	
}