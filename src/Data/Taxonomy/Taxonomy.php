<?php

namespace WordPress\Data\Taxonomy;

use WordPress\Data\Entity;

class Taxonomy extends Entity
{
	
	const DEFAULT_TAXONOMY = 'category';
	
	public $name;
	public $hierarchical;
	public $update_count_callback;
	public $rewrite;
	public $query_var;
	public $public;
	public $show_ui;
	public $show_tagcloud;
	public $_builtin;
	public $labels;
	public $show_in_nav_menus;
	public $label;
	public $singular_label;
	public $cap;
	public $sort;
	public $object_type;
	
	protected $uri;
	
	public function __construct($data = null) {
		if (! isset($data)) {
			$data = self::DEFAULT_TAXONOMY;
		}
		if (is_string($data)) {
			$data = get_taxonomy($data);
		}
		parent::__construct($data);
	}
	
	public function getRepository() {
		return di('taxonomies');
	}
	
	public function isCustomType() {	
		if (! taxonomy_exists($this->name)) {
			return false;
		}
		return ! in_array($this->name, array(
			'category',
			'post_tag',
			'post_format',
			'nav_menu',
			'link_category'
		));
	}
	
	public function isBuiltin() {
		return $this->_builtin;
	}
	
	public function __toString() {
		return $this->name;
	}
	
}
