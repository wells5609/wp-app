<?php

namespace WordPress\Model\Post\Meta;

use WordPress\Model\StorageInterface;

class Storage implements StorageInterface
{
	
	/**
	 * Returns the storage name/identifier.
	 * 
	 * @return string
	 * 		Identifier for the storage container.
	 */
	public function getName() {
		return 'postmeta';
	}
	
	protected function extractVar(array $args, array $vars, $unset = false) {
		foreach($vars as $var) {
			if (isset($args[$var])) {
				$value = $args[$var];
				if ($unset) {
					unset($args[$var]);
				}
				return $value;
			}
		}
		return null;
	}
	
	protected function extractPostId(array $where, $unset = false) {
		return $this->extractVar($where, array('post_id', 'ID', 'id'), $unset);
	}
	
	protected function extractMetaKey(array $where, $unset = false) {
		return $this->extractVar($where, array('meta_key', 'key'), $unset);
	}
	
	protected function extractMetaValue(array $where, $unset = false) {
		return $this->extractVar($where, array('meta_value', 'value'), $unset);
	}
	
	/**
 	 * Locates and returns multiple records.
	 *
	 * @param array $where
	 * 
	 * @return array
	 * 		Array of implementation-defined values.
	 */	
	public function find(array $where) {
		$postId = $this->extractPostId($where);
		if (! $postId) {
			throw new RuntimeException("Missing 'post_id' argument");
		}
		$postmeta = get_post_meta($postId);
		return empty($postmeta) ? array() : array_map('WordPress\Model\Post\Meta\Meta::forgeObject', $postmeta);
	}
	
	/**
 	 * Locates and returns a single record.
	 *
	 * @param array $where
	 * 
	 * @return mixed
	 * 		Implementation-defined return value.
	 */
	public function findOne(array $where) {
		$postId = $this->extractPostId($where);
		if (! $postId) {
			throw new RuntimeException("Missing 'post_id' argument");
		}
		if (! $metaKey = $this->extractMetaKey($where)) {
			throw new RuntimeException("Missing 'meta_key' argument");
		}
		$postmeta = get_post_meta($postId, $metaKey);
		if (empty($postmeta)) {
			return null;
		}
		if (is_array($postmeta)) {
			$postmeta = reset($postmeta);
		}
		return Meta::forgeObject($postmeta);
	}
	
	/**
	 * Locates and returns a record by a field.
	 *
	 * @param string $field 
	 * 		Name of the field to search by (e.g. in a relational database, this would be a column). 
	 * @param string $value 
	 * 		The column value to search for.
	 * @param array $where [Optional] 
	 * 		Associative array of extra `WHERE` clause arguments.
	 * 
	 * @return mixed
	 * 		Implementation-defined return value.
	 */	
	public function findOneBy($column, $value, array $where = array()) {
		if ($column !== 'meta_key' && $column !== 'key') {
			throw new InvalidArgumentException("Invalid column '$column'");
		}
		$where[$column] = $value;
		return static::findOne($where);
	}
	
	/**
	 * Insert a record into storage.
	 * 
	 * @param array $data 
	 * 		Associative array of record data to insert.
	 * 
	 * @return mixed
	 * 		Implementation-defined return value.
	 */
	public function insert(array $data) {
		return $this->update($data, array());
	}
	
	/**
	 * Update a record in storage.
	 * 
	 * @param array $data 
	 * 		Associative array of record data to update.
	 * @param array $where 
	 * 		Arguments that identify the record to be updated.
	 * 
	 * @return mixed
	 * 		Implementation-defined return value.
	 */
	 public function update(array $data, array $where) {
	 	$data = array_merge($data, $where);
		if (! $postId = $this->extractPostId($data)) {
			throw new RuntimeException("Missing 'post_id' argument");
		}
		if (! $metaKey = $this->extractMetaKey($data)) {
			throw new RuntimeException("Missing 'meta_key' argument");
		}
		if (! $metaValue = $this->extractMetaValue($data)) {
			throw new RuntimeException("Missing 'meta_value' argument");
		}
		if (isset($data['prev_value'])) {
			return (bool)update_post_meta($postId, $metaKey, $metaValue, $data['prev_value']);
		}
		return (bool)update_post_meta($postId, $metaKey, $metaValue);
	 }
	
	/**
	 * Delete a record from storage.
	 * 
	 * @param array $where 
	 * 		Arguments that identify the record to be updated.
	 * 
	 * @return mixed
	 * 		Implementation-defined return value.
	 */
	public function delete(array $where) {
		if (! $postId = $this->extractPostId($where)) {
			throw new RuntimeException("Missing 'post_id' argument");
		}
		if (! $metaKey = $this->extractMetaKey($where)) {
			throw new RuntimeException("Missing 'meta_key' argument");
		}
		if ($metaValue = $this->extractMetaValue($where)) {
			return (bool)delete_post_meta($postId, $metaKey, $metaValue);
		}
		return (bool)delete_post_meta($postId, $metaKey);
	}
	
}
