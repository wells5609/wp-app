<?php

/**
 * Registers a model with the manager.
 * 
 * @param \WordPress\Model\DefinitionInterface $definition
 * @return void
 */
function register_model(WordPress\Model\DefinitionInterface $definition) {
	WordPress\App::instance()->get('modelManager')->register($definition);
}

/**
 * Returns the definition for a model.
 * 
 * @param string $name
 * @return \WordPress\Model\DefinitionInterface
 */
function model_definition($name) {
	return WordPress\App::instance()->get('modelManager')->getDefinition($name);
}

/**
 * Returns a registered model.
 * 
 * @param string $name
 * @return \WordPress\Model\ModelInterface
 */
function model_class($name) {
	return WordPress\App::instance()->get('modelManager')->getClassName($name);
}

/**
 * Returns an instance of a model.
 * 
 * @param string $name
 * @param mixed $data
 * @return \WordPress\Model\ModelInterface
 */
function model_instance($name, $data) {
	return WordPress\App::instance()->get('modelManager')->getInstance($name, $data);
}

function model_find($name, array $where) {
	if (! $definition = model_definition($name)) {
		return null;
	}
	$class = $definition->getClassName();
	$results = $definition->getStorage()->find($where);
	return empty($results) ? array() : array_map($class.'::forgeObject', $results);
}

function model_find_one($name, array $where) {
	if (! $definition = model_definition($name)) {
		return null;
	}
	$class = $definition->getClassName();
	$results = $definition->getStorage()->findOne($where);
	return empty($results) ? null : $class::forgeObject($results);
}
