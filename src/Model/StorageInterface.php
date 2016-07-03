<?php

namespace WordPress\Model;

interface StorageInterface 
{
	
	/**
	 * Returns the storage name/identifier.
	 * 
	 * @return string
	 * 		Identifier for the storage container.
	 */
	public function getName();
	
	/**
 	 * Locates and returns multiple records.
	 *
	 * @param array $where
	 * 
	 * @return array
	 * 		Array of implementation-defined values.
	 */	
	public function find(array $where);
	
	/**
 	 * Locates and returns a single record.
	 *
	 * @param array $where
	 * 
	 * @return mixed
	 * 		Implementation-defined return value.
	 */
	public function findOne(array $where);
	
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
	public function findOneBy($column, $value, array $where = array());
	
	/**
	 * Insert a record into storage.
	 * 
	 * @param array $data 
	 * 		Associative array of record data to insert.
	 * 
	 * @return mixed
	 * 		Implementation-defined return value.
	 */
	public function insert(array $data);
	
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
	 public function update(array $data, array $where);
	
	/**
	 * Delete a record from storage.
	 * 
	 * @param array $where 
	 * 		Arguments that identify the record to be updated.
	 * 
	 * @return mixed
	 * 		Implementation-defined return value.
	 */
	public function delete(array $where);
	
}
