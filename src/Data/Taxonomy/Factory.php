<?php

namespace WordPress\Data\Taxonomy;

use WordPress\Data\Factory as BaseFactory;

class Factory extends BaseFactory
{
	
	protected $defaultClass = 'WordPress\Data\Taxonomy\Taxonomy';
	
	public function create($taxonomy) {
		$taxonomy_name = is_object($taxonomy) ? $taxonomy->name : $taxonomy;
		return $this($taxonomy, $this->getClass($taxonomy_name));
	}
	
}
