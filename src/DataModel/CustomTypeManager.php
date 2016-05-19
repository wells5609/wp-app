<?php

namespace WordPress\DataModel;

use WordPress\Di\Injectable;
use WordPress\Post\Type\Custom as CustomPostType;
use WordPress\Taxonomy\Custom as CustomTaxonomy;
use WordPress\Database\Table\Schema;
use InvalidArgumentException;

class CustomTypeManager extends Injectable
{
	
	protected $postTypes = array();
	protected $taxonomies = array();
	protected $statuses = array();
	protected $dataTypes = array();
	
	public function load() {
		$path = $this->getDI()->get('app')->getCustomObjectsPath();
		foreach(array('post-types', 'statuses', 'taxonomies') as $dir) {
			if (is_dir($path.$dir)) {
				foreach(glob($path.$dir.'/*.php') as $__file) {
					$this->register(include $__file);
				}
			}
		}
	}
	
	public function register($object) {
		if ($object instanceof CustomPostType) {
			$this->registerPostType($object);
		} else if ($object instanceof CustomTaxonomy) {
			$this->registerTaxonomy($object);
		} else if ($object instanceof Schema) {
			$this->registerDataType($object);
		} else {
			throw new InvalidArgumentException("Invalid custom type class: ".get_class($object));
		}
	}
	
	public function registerPostType(CustomPostType $postType) {
		$postType->register();
		if (isset($postType->class)) {
			$this->getDI()->get('postFactory')->setClass($postType->slug, $postType->class);
		}
		$this->postTypes[$postType->slug] = $postType;
	}
	
	public function registerTaxonomy(CustomTaxonomy $taxonomy) {
		$taxonomy->register();
		$this->taxonomies[$taxonomy->name] = $taxonomy;
	}
	
	public function registerDataType(Schema $schema, Model $model = null) {
		if (! isset($model)) {
			$model = new Model($schema);
		}
		$this->dataTypes[$schema->name] = $model;
	}
	
	public function getModel($dataType) {
		return isset($this->dataTypes[$dataType]) ? $this->dataTypes[$dataType] : null;
	}
	
}
