<?php

namespace WordPress\Model\Taxonomy;

use WordPress\Model\AbstractModel;

class Taxonomy extends AbstractModel
{
	
	public $name;
	public $label;
	public $singular_label;
	public $query_var;
	public $public;
	public $hierarchical;
	public $update_count_callback;
	public $show_ui;
	public $show_tagcloud;
	public $show_in_nav_menus;
	public $cap;
	public $rewrite;
	public $object_type;
	public $labels;
	
}
