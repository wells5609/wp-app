<?php

namespace WordPress\Model;

interface RelationshipInterface
{
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Model\DefinitionInterface $definition
	 * @param \WordPress\Model\DefinitionInterface $related_definition
	 */
	public function __construct(DefinitionInterface $definition, DefinitionInterface $related_definition);
	
	/**
	 * Sets the key.
	 * 
	 * @param string $key
	 */
	public function setKey($key);
	
	/**
	 * Returns the key.
	 * 
	 * @return string
	 */
	public function getKey();
	
	/**
	 * Sets the foreign key.
	 * 
	 * @param string $key
	 */
	public function setForeignKey($key);
	
	/**
	 * Returns the foreign key.
	 * 
	 * @return string
	 */
	public function getForeignKey();
	
	/**
	 * Returns the model definition.
	 * 
	 * @return \WordPress\Model\DefinitionInterface
	 */
	public function getModelDefinition();
	
	/**
	 * Returns the related model definition.
	 * 
	 * @return \WordPress\Model\DefinitionInterface
	 */
	public function getRelatedModelDefinition();
	
	/**
	 * Returns the model name.
	 * 
	 * @return string
	 */
	public function getModelName();
	
	/**
	 * Returns the related model name.
	 * 
	 * @return string
	 */
	public function getRelatedModelName();
	
	/**
	 * Returns the related records for a model.
	 * 
	 * @param \WordPress\Model\ModelInterface $model
	 * @return mixed
	 */
	public function getRelatedRecords(ModelInterface $model);
	
}
