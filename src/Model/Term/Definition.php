<?php

namespace WordPress\Model\Term;

use WordPress\Model\Definition as BaseDefinition;

class Definition extends BaseDefinition
{

	final public function getName() {
		return 'term';
	}

	final public function getTableName() {
		return 'terms';
	}

	final public function getPrimaryKey() {
		return 'term_id';
	}

	public function getClassName() {
		return 'WordPress\Model\Term\Term';
	}

	final public function getColumnMap() {
		return array(
			'term_id' => 'term_id',
			'name' => 'name',
			'slug' => 'slug',
			'term_group' => 'term_group',
			'term_taxonomy_id' => '',
			'taxonomy' => '',
			'description' => '',
			'parent' => '',
			'count' => '',
		);
	}

}
