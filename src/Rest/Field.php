<?php

namespace WordPress\Rest;

class Field
{

	protected $objectType;
	protected $field;
	protected $getCallback;
	protected $updateCallback;
	protected $schema;

	public function __construct($objectType, $field, callable $getCallback = null, callable $updateCallback = null, $schema = null) {
		$this->objectType = $objectType;
		$this->field = $field;
		$this->getCallback = $getCallback;
		$this->updateCallback = $updateCallback;
		$this->schema = (object)($schema ?: []);
		add_action('rest_api_init', array($this, 'register'));
	}

	public function setGetCallback(callable $callback) {
		$this->getCallback = $callback;
	}

	public function setUpdateCallback(callable $callback) {
		$this->updateCallback = $callback;
	}

	public function setSchema($schema) {
		$this->schema = (object)$schema;
	}
	
	public function setDescription($text) {
		$this->schema->description = $text;
	}
	
	public function setType($type) {
		$this->schema->type = $type;
	}
	
	public function setContext($context) {
		$this->schema->context = $context;
	}

	public function register() {
		$args = array_filter(array(
			'get_callback' => $this->getCallback,
			'update_callback' => $this->updateCallback,
			'schema' => (array)$this->schema
		));
		register_rest_field($this->objectType, $this->field, $args);
	}

}
