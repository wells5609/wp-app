<?php

namespace WordPress\Model\Option;

use WordPress\Model\Definition as BaseDefinition;

class Definition extends BaseDefinition
{
	
	final public function getName() {
		return 'option';
	}
	
	final public function getTableName() {
		return 'options';
	}
	
	final public function getPrimaryKey() {
		return 'option_id';
	}
	
	public function getClassName() {
		return 'WordPress\Model\Option\Option';
	}
	
	final public function getColumnMap() {
		return array(
			'option_id' => 'option_id',
			'option_name' => 'option_name',
			'option_value' => 'option_value',
			'autoload' => 'autoload',
		);
	}
	
}
