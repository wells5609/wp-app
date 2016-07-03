<?php

namespace WordPress\Model\Relationship;

use WordPress\Model\RelationshipInterface;
use WordPress\Model\DefinitionInterface;
use InvalidArgumentException;

abstract class Relationship implements RelationshipInterface
{
	
	/**
	 * The model's definition.
	 * 
	 * @var \WordPress\Model\DefinitionInterface
	 */
	protected $definition;
	
	/**
	 * The related model's definition.
	 * 
	 * @var \WordPress\Model\DefinitionInterface
	 */
	protected $relatedDefinition;
	
	/**
	 * The key used to match related objects.
	 * 
	 * Defaults to the model's primary key.
	 * 
	 * @var string 
	 */
	protected $key;
	
	/**
	 * The related object's key that corresponds to the key.
	 * 
	 * @var string
	 */
	protected $foreignKey;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Model\DefinitionInterface $definition
	 * @param \WordPress\Model\DefinitionInterface $related_definition
	 */
	public function __construct(DefinitionInterface $definition, DefinitionInterface $related_definition) {
		$this->definition = $definition;
		$this->key = $this->definition->getPrimaryKey();
		$this->relatedDefinition = $related_definition;
		$this->foreignKey = $this->definition->getName().'_'.$this->key;
	}
	
	/**
	 * Sets the key.
	 * 
	 * @param string $key
	 */
	public function setKey($key) {
		$this->assertValidKey($key);
		$this->key = $key;
	}
	
	/**
	 * Returns the key.
	 * 
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}
	
	/**
	 * Sets the foreign key.
	 * 
	 * @param string $key
	 */
	public function setForeignKey($key) {
		$this->assertValidForeignKey($key);
		$this->foreignKey = $key;
	}
	
	/**
	 * Returns the foreign key.
	 * 
	 * @return string
	 */
	public function getForeignKey() {
		return $this->foreignKey;
	}
	
	/**
	 * Returns the model definition.
	 * 
	 * @return \WordPress\Model\DefinitionInterface
	 */
	public function getModelDefinition() {
		return $this->definition;
	}
	
	/**
	 * Returns the related model definition.
	 * 
	 * @return \WordPress\Model\DefinitionInterface
	 */
	public function getRelatedModelDefinition() {
		return $this->relatedDefinition;
	}
	
	/**
	 * Returns the model name.
	 * 
	 * @return string
	 */
	public function getModelName() {
		return $this->definition->getName();
	}
	
	/**
	 * Returns the related model name.
	 * 
	 * @return string
	 */
	public function getRelatedModelName() {
		return $this->relatedDefinition->getName();
	}
	
	/**
	 * Asserts the key is valid for the base model.
	 */
	protected function assertValidKey($key) {
		if (! in_array($key, $this->definition->getColumnMap(), true)) {
			throw new InvalidArgumentException("Invalid key '{$key}' for '{$this->definition->getName()}'");
		}
	}
	
	/**
	 * Asserts the key is valid for the related model.
	 */
	protected function assertValidForeignKey($key) {
		if (! in_array($key, $this->relatedDefinition->getColumnMap(), true)) {
			throw new InvalidArgumentException("Invalid key '{$key}' for '{$this->relatedDefinition->getName()}'");
		}
	}
	
}
