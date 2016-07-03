<?php

namespace WordPress\Model;

class Manager
{

	/**
	 * Array of model definitions.
	 * 
	 * @var array
	 */
	protected $definitions = [];

	/**
	 * Map of model classes to names.
	 * 
	 * @var array
	 */
	protected $classMap = [];
	
	/**
	 * Registers a model definition.
	 * 
	 * @param \WordPress\Model\DefinitionInterface $definition
	 * 
	 * @return \WordPress\Model\Manager
	 */
	public function register(DefinitionInterface $definition) {
		$this->definitions[$definition->getName()] = $definition;
		$this->classMap[$definition->getClassName()] = $definition->getName();
		return $this;
	}
	
	/**
	 * Initializes registered model definitions.
	 * 
	 * This method allows relationships to be defined in the initialize() method.
	 * 
	 * @return int Number of definitions initialized
	 */
	public function init() {
		$num = 0;
		foreach($this->definitions as $definition) {
			if (method_exists($definition, 'initialize')) {
				$definition->initialize();
				$num += 1;
			}
		}
		return $num;
	}

	/**
	 * Returns a model definition.
	 * 
	 * @param string $name
	 * @param boolean $search_by_table [Optional] Default = true
	 * 
	 * @return \WordPress\Model\DefinitionInterface
	 */
	public function getDefinition($name, $search_by_table = true) {
		if (isset($this->definitions[$name])) {
			return $this->definitions[$name];
		} else if (isset($this->classMap[$name])) {
			return $this->definitions[$this->classMap[$name]];
		} else if ($search_by_table && $name = $this->findNameByTable($name)) {
			return $this->definitions[$name];
		}
		return null;
	}
	
	/**
	 * Returns the definition for a given model.
	 * 
	 * @param \WordPress\Model\ModelInterface $model
	 * 
	 * @return \WordPress\Model\DefinitionInterface
	 */
	public function getDefinitionOf(ModelInterface $model) {
		$class = get_class($model);
		if (! isset($this->classMap[$class])) {
			$class = get_parent_class($model);
			if (! $class || ! isset($this->classMap[$class])) {
				return null;
			}
		}
		return $this->definitions[$this->classMap[$class]];
	}
	
	/**
	 * Returns the model's table name.
	 * 
	 * @param string $name
	 * 
	 * @return string
	 */
	public function getTableName($name) {
		if ($def = $this->getDefinition($name, false)) {
			return $def->getStorage()->getName();
		}
	}

	/**
	 * Returns the model's class name.
	 * 
	 * @param string $name
	 * 
	 * @return string
	 */
	public function getClassName($name) {
		if ($def = $this->getDefinition($name)) {
			return $def->getClassName();
		}
	}
	
	/**
	 * Returns the model's relationships.
	 * 
	 * @param string $name
	 * 
	 * @return array
	 */
	public function getRelationships($name) {
		if ($def = $this->getDefinition($name)) {
			return $def->getRelationships();
		}
	}

	/**
	 * Returns an instance of a model.
	 * 
	 * @param string $name
	 * @param mixed $data
	 * 
	 * @return \WordPress\Model\ModelInterface
	 */
	public function getInstance($name, $data) {
		if ($class = $this->getClassName($name)) {
			return $class::forgeObject($data);
		}
	}

	/**
	 * Attempts to find a model name by its table name.
	 * 
	 * @param string $table
	 * 
	 * @return string|null
	 */
	protected function findNameByTable($table) {
		foreach($this->definitions as $object) {
			if ($object->getStorage()->getName() === $table) {
				return $object->getName();
			}
		}
	}

}
