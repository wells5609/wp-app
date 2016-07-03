<?php

namespace WordPress\Data;

interface ModelInterface extends \Countable, \Serializable, \ArrayAccess
{

	/**
	 * Construct the model optionally with initial data.
	 *
	 * @param mixed $data [Optional]
	 */
	public function __construct($data = null);
	
	/**
	 * Sets the model's storage container.
	 * 
	 * @param \WordPress\Data\StorageInterface $storage
	 */
	public function setModelStorage(StorageInterface $storage);

	/**
	 * Returns the model's storage container.
	 *
	 * @return \WordPress\Data\StorageInterface
	 */
	public function getModelStorage();

	/**
	 * Returns an array of data to save to storage.
	 *
	 * @return array
	 */
	public function getModelData();

	/**
	 * Hydrates the model with the given data.
	 *
	 * @param mixed $data
	 */
	public function hydrate($data);

	/**
	 * Returns the model as an associative array.
	 *
	 * @return array
	 */
	public function toArray();

	/**
	 * Returns a map (assoc. array) of the model's properties and storage columns.
	 *
	 * @return array
	 */
	public function columnMap();
	
	/**
	 * Returns the unique identifier for the model.
	 * 
	 * @return int|string
	 */
	public function getId();
	
	/**
	 * Saves the model to storage.
	 * 
	 * @return boolean
	 */
	public function save();

}
