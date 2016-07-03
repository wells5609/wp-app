<?php

namespace WordPress\Model;

interface DefinitionInterface
{
	
	/**
	 * Returns the model's backend storage container.
	 * 
	 * @return \WordPress\Model\StorageInterface
	 */
	public function getStorage();
	
	/**
	 * Returns the model name.
	 * 
	 * @return string
	 */
	public function getName();
	
	/**
	 * Returns the primary key name.
	 * 
	 * @return string
	 */
	public function getPrimaryKey();
	
	/**
	 * Returns the model class name.
	 * 
	 * @return string
	 */
	public function getClassName();
	
	/**
	 * Returns a map of class properties to table columns.
	 * 
	 * @return array
	 */
	public function getColumnMap();
	
	/**
	 * Returns the model's relationships.
	 * 
	 * @return array
	 */
	public function getRelationships();
	
	/**
	 * Returns a relationship for the given related model.
	 * 
	 * @param string $name
	 * @return \WordPress\Model\RelationshipInterface
	 */
	public function getRelationship($name);
	
}
