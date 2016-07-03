<?php

namespace WordPress\Model\Post\Meta;

use WordPress\Model\Definition as BaseDefinition;

class Definition extends BaseDefinition
{
	
	final public function getName() {
		return 'postmeta';
	}
	
	final public function getTableName() {
		return 'postmeta';
	}
	
	final public function getPrimaryKey() {
		return 'meta_id';
	}
	
	public function getClassName() {
		return 'WordPress\Model\Post\Meta\Meta';
	}
	
	final public function getColumnMap() {
		return array(
			'meta_id' => 'meta_id',
			'post_id' => 'post_id',
			'meta_key' => 'meta_key',
			'meta_value' => 'meta_value',
		);
	}
	
}
