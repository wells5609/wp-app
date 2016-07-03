<?php

namespace WordPress\Model\Post;

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
		return 'posts';
	}
	
	protected function extractPostId(array $args) {
		foreach(array('ID', 'id', 'post_id') as $var) {
			if (isset($args[$var])) {
				return $args[$var];
			}
		}
		return null;
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
		$posts = get_posts($where);
		return empty($posts) ? array() : array_map('WordPress\Model\Post\Post::forgeObject', $posts);
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
		if ($postId = $this->extractPostId($where)) {
			return Post::forgeObject(get_post($postId));
		}
		$posts = get_posts($where);
		return empty($posts) ? null : Post::forgeObject(reset($posts));
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
		if (isset($data['ID'])) {
			throw new RuntimeException("Cannot insert: post already exists.");
		}
		return (int)wp_insert_post($data);
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
		if (! isset($where['ID']) && ! isset($data['ID'])) {
			throw new RuntimeException("Missing Post ID");
		}
		return (bool)wp_update_post(array_merge(array(), $where, $data));
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
		if (! isset($where['ID'])) {
			throw new RuntimeException("Missing Post 'ID'.");
		}
		return (bool)wp_delete_post($where['ID']);
	}
	
}
