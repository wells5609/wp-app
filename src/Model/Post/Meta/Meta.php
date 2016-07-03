<?php

namespace WordPress\Model\Post\Meta;

use WordPress\Model\AbstractModel;
use RuntimeException;

class Meta extends AbstractModel
{
	
	/**
	 * @var int
	 */
	public $meta_id;
	
	/**
	 * @var int
	 */
	public $post_id;
	
	/**
	 * @var string
	 */
	public $meta_key;
	
	/**
	 * @var string
	 */
	public $meta_value;
	
	/**
	 * Returns the value of the primary key (meta_id).
	 * 
	 * @return int
	 */
	public function getPrimaryKeyValue() {
		return $this->meta_id;
	}
	
	/**
	 * Imports data into the object.
	 * 
	 * @param mixed $data
	 * @return void
	 */
	public function import($data) {
		if (! is_array($data)) {
			$data = is_object($data) ? get_object_vars($data) : (array)$data;
		}
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
		$this->meta_value = maybe_unserialize($this->meta_value);
	}
	
	/**
	 * Saves the metadata.
	 * 
	 * @param array $data [Optional]
	 * @return boolean
	 */
	public function save(array $data = null) {
		if (isset($this->meta_id)) {
			$result = $this->update($data);
		} else {
			$result = $this->insert($data);
		}
		return (bool)$result;
	}
	
	/**
	 * Insert the metadata.
	 * 
	 * @uses add_post_meta
	 * 
	 * @param array $data [Optional]
	 * @return int ID of the newly inserted record, or 0 if failed.
	 */
	public function insert(array $data = null) {
		if (isset($this->meta_id)) {
			throw new RuntimeException("Cannot insert: post already exists.");
		}
		$this->beforeInsert();
		if (isset($data)) {
			$this->import($data);
		}
		$result = add_post_meta($this->post_id, $this->meta_key, $this->meta_value);
		if ($result) {
			$this->meta_id = (int)$result;
		}
		$this->afterInsert($result);
		return $result;
	}
	
	/**
	 * Update the post.
	 * 
	 * @uses update_post_meta()
	 * 
	 * @param array $data [Optional]
	 * @return boolean
	 */
	public function update(array $data = null) {
		$this->beforeUpdate();
		if (isset($data)) {
			$this->import($data);
		}
		$result = update_post_meta($this->post_id, $this->meta_key, $this->meta_value);
		if ($result && is_int($result)) {
			$this->meta_id = $result;
		}
		$this->afterUpdate($result);
		return $result;
	}
	
	/**
	 * Delete the post.
	 * 
	 * @uses delete_post_meta()
	 * 
	 * @return boolean
	 */
	public function delete() {
		$this->beforeDelete();
		if (! $this->meta_id) {
			return null;
		}
		$result = delete_post_meta($this->post_id, $this->meta_key);
		$this->afterDelete($result);
		return $result;
	}	
	
}
