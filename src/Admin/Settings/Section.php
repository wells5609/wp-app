<?php

namespace WordPress\Admin\Settings;

class Section
{

	public $id;
	public $title;
	public $desc;
	public $callback;
	protected $fields = array();

	public function __construct($id, $title) {
		$this->id = $id;
		$this->title = $title;
	}

	public function addField(Field $field) {
		$this->fields[$field->name] = $field;
	}

	public function getFields() {
		return $this->fields;
	}

}
