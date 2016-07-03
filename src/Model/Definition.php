<?php

namespace WordPress\Model;

use WordPress\App;
use WordPress\Database\Table\Gateway as DatabaseTable;

abstract class Definition implements DefinitionInterface
{
	
	/**
	 * @var \WordPress\Model\StorageInterface
	 */
	protected $storage;
	
	/**
	 * Returns the model's backend storage container.
	 * 
	 * @return \WordPress\Model\StorageInterface
	 */
	public function getStorage() {
		if (! isset($this->storage)) {
			$this->storage = new DatabaseTable(App::instance()->get('dbConnection'), $this->getName());
		}
		return $this->storage;
	}
	
	/**
	 * Returns the model's relationships.
	 * 
	 * @return array
	 */
	public function getRelationships() {
		return isset($this->relationships) ? $this->relationships : array();
	}
	
	/**
	 * Returns a relationship for a given model if it exists.
	 * 
	 * @param string $name
	 * @return \WordPress\Model\RelationshipInterface
	 */
	public function getRelationship($name) {
		return isset($this->relationships[$name]) ? $this->relationships[$name] : null;
	}
	
	/**
	 * Adds a relationship to the model.
	 * 
	 * If using this method, the definition class should declare a protected property 
	 * named $relationships set to an empty array. Otherwise, the property will be public.
	 * 
	 * @param \WordPress\Model\RelationshipInterface
	 */
	protected function addRelationship(RelationshipInterface $relationship) {
		$this->relationships[$relationship->getRelatedModelName()] = $relationship;
	}
		
}
