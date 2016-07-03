<?php

namespace WordPress\Storage;

interface RecordInterface extends \Serializable
{

	/**
	 * Construct the record optionally with initial data.
	 *
	 * @param mixed $data [Optional]
	 */
	public function __construct($data = null);
	
	/**
	 * Returns the record's storage object.
	 *
	 * @return \WordPress\Storage\StorageInterface
	 */
	public function getStorage();
	
	/**
	 * Returns the model's name.
	 *
	 * @return string
	 */
	public function getModelName();
	
	/**
	 * Returns a description of the model.
	 *
	 * @return string
	 */
	public function getModelDescription();
	
	/**
	 * Returns an array of data to save to storage.
	 *
	 * @return array
	 */
	public function getModelData();
	
	/**
	 * Returns a unique identifier for the object.
	 * 
	 * @return string
	 */
	public function getUid();

	/**
	 * Hydrates the record with the given data.
	 *
	 * @param mixed $data
	 */
	public function hydrate($data);
	
	/**
	 * Returns the record as an associative array.
	 *
	 * @return array
	 */
	public function toArray();
	
	/**
	 * Returns a map (assoc. array) of the record's properties and columns.
	 *
	 * @return array
	 */
	public function columnMap();
	
}
