<?php

namespace WordPress\Data\Term;

use WordPress\Data\Factory as BaseFactory;

class Factory extends BaseFactory
{
	
	protected $defaultClass = 'WordPress\Data\Term\Term';
	
	public function create($term) {
		$term_name = is_object($term) ? $term->slug : $term;
		return $this($term, $this->getClass($term_name));
	}
	
}
