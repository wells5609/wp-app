<?php

namespace WordPress\Data\Core;

class Taxonomy extends AbstractModel
{

	public $name;
	public $object_type;
	public $label;
	public $description;
	public $public;
	public $publicly_queryable;
	public $hierarchical;
	public $show_ui;
	public $show_in_menu;
	public $show_in_nav_menus;
	public $show_tagcloud;
	public $show_in_quick_edit;
	public $show_admin_column;
	public $show_in_rest;
	public $meta_box_cb;
	public $rewrite;
	public $query_var;
	public $update_count_callback;
	public $rest_base;
	public $rest_controller_class;
	public $cap;
	public $labels;
	public $_builtin;
	
	public function getWordPressObjectType() {
		return 'taxonomy';
	}
	
}
