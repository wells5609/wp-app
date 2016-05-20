<?php

namespace WordPress\Data\Taxonomy;

use WordPress\Data\Entity;

class Term extends Entity
{
	public $term_id;
	public $name;
	public $slug;
	public $term_group;
	public $term_taxonomy_id;
	public $taxonomy;
	public $description;
	public $parent;
	public $count;
	
	protected $uri;
	
	public function __construct($data = null) {
		if (! isset($data)) {
			$data = array('taxonomy' => self::DEFAULT_TAXONOMY);
		} else if (is_numeric($data)) {
			$data = get_term($data);
		}
		parent::__construct($data);
	}
	
	public function getRepository() {
		return di('terms');
	}
	
	public function __toString() {
		return $this->name;
	}
	
	public function getUri() {
		if (! isset($this->uri) && isset($this->term_id)) {
			$this->uri = get_term_link($this);
		}
		return $this->uri;
	}
	
}
