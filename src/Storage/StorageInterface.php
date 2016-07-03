<?php

namespace WordPress\Storage;

interface StorageInterface
{
	
	/**
	 * Returns an alphanumeric string describing the type of storage container.
	 * 
	 * @return string
	 */
	public function getTypeName();
	
	/**
	 * Returns a unique name for the storage instance.
	 * 
	 * Names need only be unique for its type.
	 * 
	 * @return string
	 */
	public function getName();
	
	/**
 	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 * 
	 * @return mixed
	 */
	public function fetch($args);
	
	/**
 	 * Retrieves a single record from storage.
	 *
	 * @param mixed $args
	 * 
	 * @return mixed
	 */
	public function fetchOne($args);
	
	/**
	 * Saves a record to storage.
	 * 
	 * @param RecordInterface $record
	 */
	public function save(RecordInterface $record);
	
	/**
	 * Deletes a record from storage.
	 * 
	 * @param RecordInterface $record
	 */
	public function delete(RecordInterface $record);
	
	/**
	 * Creates a record from data.
	 * 
	 * @param array $data
	 * 
	 * @return RecordInterface
	 */
	public function create($data);
	
}
